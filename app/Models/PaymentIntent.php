<?php

namespace App\Models;

use App\Enums\PaymentIntentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PaymentIntent extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'invoice_id',
        'amount',
        'currency',
        'status',
        'payment_method',
        'card_last4',
        'failure_code',
        'failure_message',
        'retry_count',
        'next_retry_at',
        'idempotency_key',
        'metadata',
        'confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => PaymentIntentStatus::class,
            'metadata' => 'array',
            'next_retry_at' => 'datetime',
            'confirmed_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function order(): HasOne
    {
        return $this->hasOne(Order::class);
    }
}
