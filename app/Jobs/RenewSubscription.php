<?php

namespace App\Jobs;

use App\Actions\CreatePaymentIntentAction;
use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Services\InvoiceService;
use App\Services\WebhookService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RenewSubscription implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $subscriptionId)
    {
    }

    public function handle(
        InvoiceService $invoiceService,
        CreatePaymentIntentAction $createPaymentIntentAction,
        WebhookService $webhookService
    ): void {
        $subscription = Subscription::query()->with('price')->find($this->subscriptionId);

        if (! $subscription || $subscription->status === SubscriptionStatus::Canceled) {
            return;
        }

        $invoice = $invoiceService->createForSubscription($subscription);

        $webhookService->publish('invoice.created', 'invoice', $invoice->id, $invoice->toArray());

        $createPaymentIntentAction->execute([
            'customer_id' => $subscription->customer_id,
            'invoice_id' => $invoice->id,
            'amount' => $invoice->amount_due,
            'currency' => $invoice->currency,
        ]);

        $subscription->update([
            'status' => SubscriptionStatus::Active,
            'current_period_start' => now(),
            'current_period_end' => now()->addMonths($subscription->price->interval_count),
        ]);
    }
}
