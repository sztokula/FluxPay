<?php

namespace App\Actions;

use App\Enums\PaymentIntentStatus;
use App\Models\Customer;
use App\Models\PaymentIntent;
use App\Services\WebhookService;

class CreatePaymentIntentAction
{
    public function __construct(private WebhookService $webhookService)
    {
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function execute(array $payload): PaymentIntent
    {
        $customer = Customer::query()->findOrFail($payload['customer_id']);

        $paymentIntent = PaymentIntent::query()->create([
            'customer_id' => $customer->id,
            'invoice_id' => $payload['invoice_id'] ?? null,
            'amount' => $payload['amount'],
            'currency' => strtoupper($payload['currency'] ?? 'USD'),
            'status' => PaymentIntentStatus::RequiresConfirmation,
            'idempotency_key' => $payload['idempotency_key'] ?? null,
            'metadata' => $payload['metadata'] ?? null,
        ]);

        $this->webhookService->publish(
            eventName: 'payment_intent.created',
            aggregateType: 'payment_intent',
            aggregateId: $paymentIntent->id,
            payload: $paymentIntent->toArray()
        );

        return $paymentIntent;
    }
}
