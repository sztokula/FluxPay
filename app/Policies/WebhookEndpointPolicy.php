<?php

namespace App\Policies;

use App\Models\WebhookEndpoint;
use App\Models\User;

class WebhookEndpointPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, WebhookEndpoint $webhookEndpoint): bool
    {
        return $webhookEndpoint->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, WebhookEndpoint $webhookEndpoint): bool
    {
        return $webhookEndpoint->user_id === $user->id;
    }

    public function delete(User $user, WebhookEndpoint $webhookEndpoint): bool
    {
        return $webhookEndpoint->user_id === $user->id;
    }
}
