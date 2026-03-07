<?php

namespace App\Policies;

use App\Models\LedgerEntry;
use App\Models\User;

class LedgerEntryPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, LedgerEntry $ledgerEntry): bool
    {
        return $ledgerEntry->customer?->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, LedgerEntry $ledgerEntry): bool
    {
        return false;
    }

    public function delete(User $user, LedgerEntry $ledgerEntry): bool
    {
        return false;
    }

    public function restore(User $user, LedgerEntry $ledgerEntry): bool
    {
        return false;
    }

    public function forceDelete(User $user, LedgerEntry $ledgerEntry): bool
    {
        return false;
    }
}
