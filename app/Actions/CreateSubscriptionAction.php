<?php

namespace App\Actions;

use App\Enums\SubscriptionStatus;
use App\Models\Price;
use App\Models\Subscription;
use App\Services\InvoiceService;
use App\Services\WebhookService;

class CreateSubscriptionAction
{
    public function __construct(
        private InvoiceService $invoiceService,
        private CreatePaymentIntentAction $createPaymentIntentAction,
        private WebhookService $webhookService
    ) {
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function execute(array $payload): Subscription
    {
        $price = Price::query()->findOrFail($payload['price_id']);

        $subscription = Subscription::query()->create([
            'customer_id' => $payload['customer_id'],
            'price_id' => $price->id,
            'status' => $price->trial_days > 0 ? SubscriptionStatus::Trialing : SubscriptionStatus::Active,
            'current_period_start' => now(),
            'current_period_end' => now()->addMonths($price->interval_count),
        ]);

        $invoice = $this->invoiceService->createForSubscription($subscription);

        $this->webhookService->publish(
            eventName: 'invoice.created',
            aggregateType: 'invoice',
            aggregateId: $invoice->id,
            payload: $invoice->toArray()
        );

        $this->createPaymentIntentAction->execute([
            'customer_id' => $subscription->customer_id,
            'invoice_id' => $invoice->id,
            'amount' => $invoice->amount_due,
            'currency' => $invoice->currency,
        ]);

        $this->webhookService->publish(
            eventName: 'subscription.created',
            aggregateType: 'subscription',
            aggregateId: $subscription->id,
            payload: $subscription->toArray()
        );

        return $subscription;
    }
}
