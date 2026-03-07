<?php

namespace App\Jobs;

use App\Models\EventLog;
use App\Models\WebhookDelivery;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;

class DeliverWebhookEvent implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $deliveryId)
    {
    }

    public function handle(): void
    {
        $delivery = WebhookDelivery::query()->with('endpoint')->find($this->deliveryId);

        if (! $delivery || ! $delivery->endpoint?->is_active) {
            return;
        }

        $payloadJson = json_encode($delivery->payload, JSON_THROW_ON_ERROR);
        $signature = hash_hmac('sha256', $payloadJson, $delivery->endpoint->secret);

        $response = Http::retry(2, 100, throw: false)
            ->timeout(8)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'X-Event-Name' => $delivery->event_name,
                'X-Webhook-Attempt' => (string) $delivery->attempt,
                'X-Webhook-Signature' => $signature,
            ])
            ->post($delivery->endpoint->url, $delivery->payload);

        if ($response->successful()) {
            $delivery->update([
                'last_http_code' => $response->status(),
                'last_error' => null,
                'next_attempt_at' => null,
                'delivered_at' => now(),
            ]);

            EventLog::query()->create([
                'user_id' => $delivery->endpoint->user_id,
                'event_name' => 'webhook.delivery.succeeded',
                'aggregate_type' => 'webhook_delivery',
                'aggregate_id' => $delivery->id,
                'payload' => [
                    'endpoint_id' => $delivery->webhook_endpoint_id,
                    'event_name' => $delivery->event_name,
                    'attempt' => $delivery->attempt,
                    'http_code' => $response->status(),
                ],
                'happened_at' => now(),
            ]);

            return;
        }

        $attempt = $delivery->attempt + 1;

        if ($attempt > 5) {
            $delivery->update([
                'attempt' => $attempt,
                'last_http_code' => $response->status(),
                'last_error' => 'max_retries_reached',
                'next_attempt_at' => null,
            ]);

            EventLog::query()->create([
                'user_id' => $delivery->endpoint->user_id,
                'event_name' => 'webhook.delivery.failed',
                'aggregate_type' => 'webhook_delivery',
                'aggregate_id' => $delivery->id,
                'payload' => [
                    'endpoint_id' => $delivery->webhook_endpoint_id,
                    'event_name' => $delivery->event_name,
                    'attempt' => $attempt,
                    'http_code' => $response->status(),
                    'reason' => 'max_retries_reached',
                ],
                'happened_at' => now(),
            ]);

            return;
        }

        $retrySchedule = config('payment.webhooks.retry_schedule_seconds', [60, 300, 1800, 21600, 86400]);
        $delay = $retrySchedule[$attempt - 1] ?? 86400;

        $delivery->update([
            'attempt' => $attempt,
            'last_http_code' => $response->status(),
            'last_error' => 'non_2xx_response',
            'next_attempt_at' => now()->addSeconds($delay),
        ]);

        EventLog::query()->create([
            'user_id' => $delivery->endpoint->user_id,
            'event_name' => 'webhook.delivery.retry_scheduled',
            'aggregate_type' => 'webhook_delivery',
            'aggregate_id' => $delivery->id,
            'payload' => [
                'endpoint_id' => $delivery->webhook_endpoint_id,
                'event_name' => $delivery->event_name,
                'attempt' => $attempt,
                'http_code' => $response->status(),
                'retry_in_seconds' => $delay,
            ],
            'happened_at' => now(),
        ]);

        self::dispatch($delivery->id)->delay(now()->addSeconds($delay));
    }
}
