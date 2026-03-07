<?php

namespace App\Jobs;

use App\Enums\FraudDecision;
use App\Enums\InvoiceStatus;
use App\Enums\PaymentIntentStatus;
use App\Enums\SubscriptionStatus;
use App\Models\EventLog;
use App\Models\Order;
use App\Models\PaymentIntent;
use App\Services\FraudScoringService;
use App\Services\LedgerService;
use App\Services\AppSettingsService;
use App\Services\PaymentIntentStateMachine;
use App\Services\PaymentRetryPolicy;
use App\Services\PaymentSimulator;
use App\Services\WebhookService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class ProcessPaymentIntent implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $paymentIntentId, public string $cardNumber)
    {
    }

    public function handle(
        FraudScoringService $fraudScoringService,
        PaymentSimulator $paymentSimulator,
        PaymentRetryPolicy $paymentRetryPolicy,
        PaymentIntentStateMachine $stateMachine,
        LedgerService $ledgerService,
        WebhookService $webhookService,
        ?AppSettingsService $appSettingsService = null
    ): void {
        $appSettingsService ??= app(AppSettingsService::class);

        Cache::put('system.worker_last_job_at', now()->toIso8601String(), now()->addDay());

        /**
         * Processing strategy:
         * - Fraud decision is evaluated first and can short-circuit flow.
         * - Successful authorization finalizes invoice/subscription and records ledger charge.
         * - Temporary failures stay in processing with scheduled retries.
         * - Terminal failures move invoice/subscription to uncollectible/unpaid.
         */
        $paymentIntent = PaymentIntent::query()->with(['customer', 'invoice.subscription'])->find($this->paymentIntentId);

        if (! $paymentIntent) {
            return;
        }

        $fraudDecision = $fraudScoringService->score(
            customer: $paymentIntent->customer,
            amount: $paymentIntent->amount,
            cardNumber: $this->cardNumber
        );

        EventLog::query()->create([
            'user_id' => $paymentIntent->customer->user_id,
            'event_name' => 'fraud.decision',
            'aggregate_type' => 'payment_intent',
            'aggregate_id' => $paymentIntent->id,
            'payload' => ['decision' => $fraudDecision->value],
            'happened_at' => now(),
        ]);

        if ($fraudDecision === FraudDecision::Review) {
            $paymentIntent->update([
                'status' => PaymentIntentStatus::RequiresAction,
                'failure_code' => 'fraud_review',
                'failure_message' => 'Marked for manual review.',
            ]);

            return;
        }

        if ($fraudDecision === FraudDecision::Block) {
            if ($stateMachine->canTransition($paymentIntent->status, PaymentIntentStatus::Failed)) {
                $paymentIntent->update([
                    'status' => PaymentIntentStatus::Failed,
                    'failure_code' => 'fraud_blocked',
                    'failure_message' => 'Blocked by fraud scoring.',
                ]);
            }

            if ($paymentIntent->invoice) {
                $paymentIntent->invoice->update(['status' => InvoiceStatus::Uncollectible]);
                $paymentIntent->invoice->subscription?->update(['status' => SubscriptionStatus::Unpaid]);
            }

            $webhookService->publish('payment_intent.failed', 'payment_intent', $paymentIntent->id, $paymentIntent->toArray());

            return;
        }

        $result = $paymentSimulator->process($this->cardNumber);

        if ($result === 'success') {
            if ($stateMachine->canTransition($paymentIntent->status, PaymentIntentStatus::Succeeded)) {
                $paymentIntent->update([
                    'status' => PaymentIntentStatus::Succeeded,
                    'failure_code' => null,
                    'failure_message' => null,
                    'next_retry_at' => null,
                ]);
            }

            if ($paymentIntent->invoice) {
                $paymentIntent->invoice->update([
                    'status' => InvoiceStatus::Paid,
                    'amount_paid' => $paymentIntent->invoice->amount_due,
                    'paid_at' => now(),
                ]);

                $paymentIntent->invoice->subscription?->update(['status' => SubscriptionStatus::Active]);
            }

            $ledgerService->recordCharge(
                customer: $paymentIntent->customer,
                amount: $paymentIntent->amount,
                currency: $paymentIntent->currency,
                referenceType: 'payment_intent',
                referenceId: $paymentIntent->id
            );

            $productId = data_get($paymentIntent->metadata, 'product_id');

            $autoFinalizeOrders = (bool) $appSettingsService->get('auto_finalize_orders', true);

            if ($autoFinalizeOrders && $paymentIntent->invoice_id === null && is_numeric($productId) && Schema::hasTable('orders')) {
                $order = Order::query()->firstOrCreate(
                    ['payment_intent_id' => $paymentIntent->id],
                    [
                        'customer_id' => $paymentIntent->customer_id,
                        'product_id' => (int) $productId,
                        'amount' => $paymentIntent->amount,
                        'currency' => $paymentIntent->currency,
                        'status' => 'paid',
                        'fulfilled_at' => now(),
                        'metadata' => [
                            'source' => 'storefront',
                        ],
                    ]
                );

                EventLog::query()->create([
                    'user_id' => $paymentIntent->customer->user_id,
                    'event_name' => 'order.finalized',
                    'aggregate_type' => 'order',
                    'aggregate_id' => $order->id,
                    'payload' => $order->toArray(),
                    'happened_at' => now(),
                ]);
            }

            $webhookService->publish('payment_intent.succeeded', 'payment_intent', $paymentIntent->id, $paymentIntent->toArray());

            if ($paymentIntent->invoice) {
                $webhookService->publish('invoice.paid', 'invoice', $paymentIntent->invoice->id, $paymentIntent->invoice->toArray());
            }

            return;
        }

        if ($result === 'requires_action') {
            if ($stateMachine->canTransition($paymentIntent->status, PaymentIntentStatus::RequiresAction)) {
                $paymentIntent->update([
                    'status' => PaymentIntentStatus::RequiresAction,
                    'failure_code' => 'authentication_required',
                    'failure_message' => 'Additional card authentication required.',
                ]);

                EventLog::query()->create([
                    'user_id' => $paymentIntent->customer->user_id,
                    'event_name' => 'payment_intent.requires_action',
                    'aggregate_type' => 'payment_intent',
                    'aggregate_id' => $paymentIntent->id,
                    'payload' => [
                        'failure_code' => 'authentication_required',
                    ],
                    'happened_at' => now(),
                ]);
            }

            return;
        }

        $isRetryable = $result === 'temporary_failure';
        $maxRetryAttempts = (int) $appSettingsService->get('max_retry_attempts', $paymentRetryPolicy->maxRetries());
        $nextDelay = $isRetryable ? $paymentRetryPolicy->nextDelaySeconds($paymentIntent->retry_count) : null;

        if ($isRetryable && $paymentIntent->retry_count >= $maxRetryAttempts) {
            $nextDelay = null;
        }

        if ($nextDelay === null) {
            if ($stateMachine->canTransition($paymentIntent->status, PaymentIntentStatus::Failed)) {
                $paymentIntent->update([
                    'status' => PaymentIntentStatus::Failed,
                    'failure_code' => $result,
                    'failure_message' => 'Payment failed after retries.',
                    'next_retry_at' => null,
                ]);
            }

            if ($paymentIntent->invoice) {
                $paymentIntent->invoice->update(['status' => InvoiceStatus::Uncollectible]);
                $paymentIntent->invoice->subscription?->update(['status' => SubscriptionStatus::Unpaid]);
            }

            $webhookService->publish('payment_intent.failed', 'payment_intent', $paymentIntent->id, $paymentIntent->toArray());

            return;
        }

        if ($paymentIntent->invoice?->subscription) {
            $paymentIntent->invoice->subscription->update(['status' => SubscriptionStatus::PastDue]);
        }

        $paymentIntent->update([
            'status' => PaymentIntentStatus::Processing,
            'failure_code' => $result,
            'failure_message' => 'Temporary failure, retry scheduled.',
            'retry_count' => $paymentIntent->retry_count + 1,
            'next_retry_at' => now()->addSeconds($nextDelay),
        ]);

        EventLog::query()->create([
            'user_id' => $paymentIntent->customer->user_id,
            'event_name' => 'payment_intent.retry_scheduled',
            'aggregate_type' => 'payment_intent',
            'aggregate_id' => $paymentIntent->id,
            'payload' => [
                'failure_code' => $result,
                'retry_count' => $paymentIntent->retry_count,
                'retry_in_seconds' => $nextDelay,
            ],
            'happened_at' => now(),
        ]);

        RetryPaymentIntent::dispatch($paymentIntent->id, $this->cardNumber)->delay(now()->addSeconds($nextDelay));
    }
}
