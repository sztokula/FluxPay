<?php

namespace App\Models;

use App\Enums\LedgerEntryType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LedgerEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'type',
        'reference_type',
        'reference_id',
        'currency',
        'amount',
        'direction',
        'description',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'type' => LedgerEntryType::class,
            'metadata' => 'array',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
