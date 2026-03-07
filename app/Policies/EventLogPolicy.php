<?php

namespace App\Policies;

use App\Models\EventLog;
use App\Models\User;

class EventLogPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, EventLog $eventLog): bool
    {
        return $eventLog->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, EventLog $eventLog): bool
    {
        return false;
    }

    public function delete(User $user, EventLog $eventLog): bool
    {
        return false;
    }

    public function restore(User $user, EventLog $eventLog): bool
    {
        return false;
    }

    public function forceDelete(User $user, EventLog $eventLog): bool
    {
        return false;
    }
}
