<?php

namespace App\Services;

use App\Jobs\DeliverWebhookEvent;
use App\Models\Customer;
use App\Models\EventLog;
use App\Models\Invoice;
use App\Models\PaymentIntent;
use App\Models\Payout;
use App\Models\Subscription;
use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;

class WebhookService
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function publish(string $eventName, string $aggregateType, int $aggregateId, array $payload): void
    {
        $userId = $this->resolveUserId($aggregateType, $aggregateId);

        EventLog::query()->create([
            'user_id' => $userId,
            'event_name' => $eventName,
            'aggregate_type' => $aggregateType,
            'aggregate_id' => $aggregateId,
            'payload' => $payload,
            'happened_at' => now(),
        ]);

        if ($userId === null) {
            return;
        }

        WebhookEndpoint::query()
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->get()
            ->each(function (WebhookEndpoint $endpoint) use ($eventName, $payload): void {
                $events = $endpoint->events ?? [];

                if (! in_array($eventName, $events, true)) {
                    return;
                }

                $delivery = WebhookDelivery::query()->create([
                    'webhook_endpoint_id' => $endpoint->id,
                    'event_name' => $eventName,
                    'payload' => $payload,
                    'attempt' => 1,
                ]);

                DeliverWebhookEvent::dispatch($delivery->id);
            });
    }

    private function resolveUserId(string $aggregateType, int $aggregateId): ?int
    {
        return match ($aggregateType) {
            'payment_intent' => $this->resolveCustomerOwnerId(
                PaymentIntent::query()->whereKey($aggregateId)->value('customer_id')
            ),
            'invoice' => $this->resolveCustomerOwnerId(
                Invoice::query()->whereKey($aggregateId)->value('customer_id')
            ),
            'subscription' => $this->resolveCustomerOwnerId(
                Subscription::query()->whereKey($aggregateId)->value('customer_id')
            ),
            'payout' => $this->resolveCustomerOwnerId(
                Payout::query()->whereKey($aggregateId)->value('customer_id')
            ),
            'webhook_endpoint' => WebhookEndpoint::query()->whereKey($aggregateId)->value('user_id'),
            default => null,
        };
    }

    private function resolveCustomerOwnerId(?int $customerId): ?int
    {
        if ($customerId === null) {
            return null;
        }

        return Customer::query()->whereKey($customerId)->value('user_id');
    }
}
