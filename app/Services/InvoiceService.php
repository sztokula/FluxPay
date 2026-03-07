<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Subscription;

class InvoiceService
{
    public function createForSubscription(Subscription $subscription): Invoice
    {
        $price = $subscription->price;

        return Invoice::query()->create([
            'customer_id' => $subscription->customer_id,
            'subscription_id' => $subscription->id,
            'amount_due' => $price->amount,
            'amount_paid' => 0,
            'currency' => $price->currency,
            'status' => InvoiceStatus::Open,
            'due_at' => now()->addDay(),
        ]);
    }
}
