<?php

namespace App\Console\Commands;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentIntentStatus;
use App\Enums\SubscriptionStatus;
use App\Models\EventLog;
use App\Models\PaymentIntent;
use App\Services\AppSettingsService;
use App\Services\WebhookService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class MarkStalePaymentIntentsFailed extends Command
{
    protected $signature = 'payment-intents:mark-stale-processing';

    protected $description = 'Mark stale processing payment intents as failed when retry execution is overdue';

    public function handle(WebhookService $webhookService, AppSettingsService $appSettingsService): int
    {
        Cache::put('system.watchdog_last_run_at', now()->toIso8601String(), now()->addDay());

        if (! (bool) $appSettingsService->get('enable_processing_watchdog', true)) {
            $this->info('Watchdog is disabled by settings.');

            return self::SUCCESS;
        }

        $noRetryThresholdMinutes = (int) config('payment.processing_stale_no_retry_minutes', 2);
        $retryGraceMinutes = (int) config('payment.processing_retry_grace_minutes', 5);

        $staleIntents = PaymentIntent::query()
            ->with(['customer', 'invoice.subscription'])
            ->where('status', PaymentIntentStatus::Processing->value)
            ->where(function ($query) use ($noRetryThresholdMinutes, $retryGraceMinutes): void {
                $query->where(function ($noRetryQuery) use ($noRetryThresholdMinutes): void {
                    $noRetryQuery
                        ->whereNull('next_retry_at')
                        ->where('confirmed_at', '<=', now()->subMinutes($noRetryThresholdMinutes));
                })->orWhere(function ($retryQuery) use ($retryGraceMinutes): void {
                    $retryQuery
                        ->whereNotNull('next_retry_at')
                        ->where('next_retry_at', '<=', now()->subMinutes($retryGraceMinutes));
                });
            })
            ->limit(100)
            ->get();

        foreach ($staleIntents as $intent) {
            $intent->update([
                'status' => PaymentIntentStatus::Failed,
                'failure_code' => 'processing_timeout',
                'failure_message' => 'Processing timed out due to missing worker execution.',
                'next_retry_at' => null,
            ]);

            if ($intent->invoice) {
                $intent->invoice->update(['status' => InvoiceStatus::Uncollectible]);
                $intent->invoice->subscription?->update(['status' => SubscriptionStatus::Unpaid]);
            }

            EventLog::query()->create([
                'user_id' => $intent->customer?->user_id,
                'event_name' => 'payment_intent.watchdog_failed',
                'aggregate_type' => 'payment_intent',
                'aggregate_id' => $intent->id,
                'payload' => [
                    'failure_code' => 'processing_timeout',
                    'retry_count' => $intent->retry_count,
                    'previous_next_retry_at' => $intent->getOriginal('next_retry_at'),
                ],
                'happened_at' => now(),
            ]);

            $webhookService->publish('payment_intent.failed', 'payment_intent', $intent->id, $intent->toArray());
        }

        $this->info('Marked stale intents as failed: '.$staleIntents->count());

        return self::SUCCESS;
    }
}
