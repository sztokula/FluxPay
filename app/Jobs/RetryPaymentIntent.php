<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RetryPaymentIntent implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $paymentIntentId, public string $cardNumber)
    {
    }

    public function handle(): void
    {
        ProcessPaymentIntent::dispatch($this->paymentIntentId, $this->cardNumber);
    }
}
