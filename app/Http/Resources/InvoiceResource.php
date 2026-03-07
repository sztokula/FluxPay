<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            'subscription_id' => $this->subscription_id,
            'amount_due' => $this->amount_due,
            'amount_paid' => $this->amount_paid,
            'currency' => $this->currency,
            'status' => $this->status->value,
            'due_at' => $this->due_at,
            'paid_at' => $this->paid_at,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at,
        ];
    }
}
