<?php

namespace App\Services;

use App\Enums\LedgerEntryType;
use App\Models\Customer;
use App\Models\LedgerEntry;

class LedgerService
{
    public function recordCharge(Customer $customer, int $amount, string $currency, string $referenceType, int $referenceId): LedgerEntry
    {
        return $this->record(
            customer: $customer,
            type: LedgerEntryType::Charge,
            amount: $amount,
            currency: $currency,
            direction: 'credit',
            referenceType: $referenceType,
            referenceId: $referenceId,
            description: 'Charge captured'
        );
    }

    public function recordPayout(Customer $customer, int $amount, string $currency, string $referenceType, int $referenceId): LedgerEntry
    {
        return $this->record(
            customer: $customer,
            type: LedgerEntryType::Payout,
            amount: $amount,
            currency: $currency,
            direction: 'debit',
            referenceType: $referenceType,
            referenceId: $referenceId,
            description: 'Payout processed'
        );
    }

    public function balance(Customer $customer): int
    {
        return (int) $customer->ledgerEntries()
            ->selectRaw("COALESCE(SUM(CASE WHEN direction = 'credit' THEN amount ELSE -amount END), 0) as balance")
            ->value('balance');
    }

    private function record(
        Customer $customer,
        LedgerEntryType $type,
        int $amount,
        string $currency,
        string $direction,
        string $referenceType,
        int $referenceId,
        ?string $description = null
    ): LedgerEntry {
        return LedgerEntry::query()->create([
            'customer_id' => $customer->id,
            'type' => $type,
            'amount' => $amount,
            'currency' => strtoupper($currency),
            'direction' => $direction,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'description' => $description,
        ]);
    }
}
