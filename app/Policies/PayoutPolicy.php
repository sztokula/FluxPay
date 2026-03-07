<?php

namespace App\Policies;

use App\Models\Payout;
use App\Models\User;

class PayoutPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Payout $payout): bool
    {
        return $payout->customer?->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }
}
