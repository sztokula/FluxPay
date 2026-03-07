<?php

namespace App\Services;

class PaymentSimulator
{
    public function process(string $cardNumber): string
    {
        return match ($cardNumber) {
            '4242424242424242' => 'success',
            '4000000000000002' => 'insufficient_funds',
            '4000000000009995' => 'temporary_failure',
            '4000000000003063' => 'requires_action',
            '4000000000000341' => 'fraud_blocked',
            default => 'failure',
        };
    }
}
