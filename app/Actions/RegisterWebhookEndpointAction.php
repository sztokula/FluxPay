<?php

namespace App\Actions;

use App\Models\WebhookEndpoint;

class RegisterWebhookEndpointAction
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function execute(array $payload): WebhookEndpoint
    {
        return WebhookEndpoint::query()->create([
            'user_id' => $payload['user_id'] ?? null,
            'url' => $payload['url'],
            'secret' => $payload['secret'] ?? bin2hex(random_bytes(16)),
            'events' => $payload['events'],
            'is_active' => $payload['is_active'] ?? true,
        ]);
    }
}
