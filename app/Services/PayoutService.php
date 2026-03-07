<?php

namespace App\Services;

use App\Enums\PayoutStatus;
use App\Models\Customer;
use App\Models\EventLog;
use App\Models\Payout;
use RuntimeException;

class PayoutService
{
    public function __construct(
        private LedgerService $ledgerService,
        private WebhookService $webhookService
    ) {
    }

    public function create(Customer $customer, int $amount, string $currency, bool $simulateFailure = false): Payout
    {
        $payout = Payout::query()->create([
            'customer_id' => $customer->id,
            'amount' => $amount,
            'currency' => strtoupper($currency),
            'status' => PayoutStatus::Pending,
        ]);

        $this->webhookService->publish('payout.created', 'payout', $payout->id, $payout->toArray());

        $payout->update(['status' => PayoutStatus::Processing]);

        $balance = $this->ledgerService->balance($customer);

        if ($simulateFailure || $amount > $balance) {
            $reason = $simulateFailure ? 'simulated_failure' : 'insufficient_balance';

            $payout->update([
                'status' => PayoutStatus::Failed,
                'failure_reason' => $reason,
                'processed_at' => now(),
            ]);

            $this->webhookService->publish('payout.failed', 'payout', $payout->id, $payout->toArray());

            throw new RuntimeException('Payout failed: '.$reason);
        }

        $this->ledgerService->recordPayout(
            customer: $customer,
            amount: $amount,
            currency: $currency,
            referenceType: 'payout',
            referenceId: $payout->id
        );

        $payout->update([
            'status' => PayoutStatus::Paid,
            'processed_at' => now(),
            'failure_reason' => null,
        ]);

        EventLog::query()->create([
            'user_id' => $customer->user_id,
            'event_name' => 'payout.paid',
            'aggregate_type' => 'payout',
            'aggregate_id' => $payout->id,
            'payload' => $payout->toArray(),
            'happened_at' => now(),
        ]);

        return $payout->fresh();
    }
}
