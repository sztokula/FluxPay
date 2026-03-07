<?php

namespace App\Services;

use App\Enums\PaymentIntentStatus;

class PaymentIntentStateMachine
{
    /**
     * @var array<string, list<string>>
     */
    private array $transitions = [
        'requires_payment_method' => ['requires_confirmation', 'canceled'],
        'requires_confirmation' => ['processing', 'canceled'],
        'processing' => ['succeeded', 'failed', 'requires_action'],
        'requires_action' => ['processing', 'requires_confirmation', 'canceled', 'failed'],
        'failed' => [],
        'succeeded' => [],
        'canceled' => [],
    ];

    public function canTransition(PaymentIntentStatus $from, PaymentIntentStatus $to): bool
    {
        return in_array($to->value, $this->transitions[$from->value] ?? [], true);
    }
}
