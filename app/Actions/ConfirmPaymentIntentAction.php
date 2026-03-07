<?php

namespace App\Actions;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentIntentStatus;
use App\Jobs\ProcessPaymentIntent;
use App\Models\EventLog;
use App\Models\PaymentIntent;
use App\Services\PaymentIntentStateMachine;

class ConfirmPaymentIntentAction
{
    public function __construct(private PaymentIntentStateMachine $stateMachine)
    {
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function execute(PaymentIntent $paymentIntent, array $payload): PaymentIntent
    {
        if (! $this->stateMachine->canTransition($paymentIntent->status, PaymentIntentStatus::Processing)) {
            return $paymentIntent;
        }

        $paymentIntent->update([
            'payment_method' => 'card',
            'card_last4' => substr($payload['card_number'], -4),
            'status' => PaymentIntentStatus::Processing,
            'confirmed_at' => now(),
        ]);

        if (app()->environment(['local', 'testing'])) {
            ProcessPaymentIntent::dispatchSync($paymentIntent->id, $payload['card_number']);
        } else {
            ProcessPaymentIntent::dispatch($paymentIntent->id, $payload['card_number']);
        }

        if ($paymentIntent->invoice) {
            $paymentIntent->invoice->update(['status' => InvoiceStatus::Open]);
        }

        EventLog::query()->create([
            'user_id' => $paymentIntent->customer?->user_id,
            'event_name' => 'payment_intent.confirmed',
            'aggregate_type' => 'payment_intent',
            'aggregate_id' => $paymentIntent->id,
            'payload' => [
                'status' => PaymentIntentStatus::Processing->value,
                'card_last4' => substr($payload['card_number'], -4),
            ],
            'happened_at' => now(),
        ]);

        return $paymentIntent->fresh();
    }

    public function cancel(PaymentIntent $paymentIntent): PaymentIntent
    {
        if (! $this->stateMachine->canTransition($paymentIntent->status, PaymentIntentStatus::Canceled)) {
            return $paymentIntent;
        }

        $paymentIntent->update([
            'status' => PaymentIntentStatus::Canceled,
            'failure_code' => 'canceled_by_user',
            'failure_message' => 'Payment canceled by customer.',
            'next_retry_at' => null,
        ]);

        EventLog::query()->create([
            'user_id' => $paymentIntent->customer?->user_id,
            'event_name' => 'payment_intent.canceled',
            'aggregate_type' => 'payment_intent',
            'aggregate_id' => $paymentIntent->id,
            'payload' => [
                'status' => PaymentIntentStatus::Canceled->value,
                'failure_code' => 'canceled_by_user',
            ],
            'happened_at' => now(),
        ]);

        return $paymentIntent->fresh();
    }
}
