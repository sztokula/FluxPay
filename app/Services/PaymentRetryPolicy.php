<?php

namespace App\Services;

class PaymentRetryPolicy
{
    /**
     * Schedule index semantics:
     * index 0 = initial attempt (immediate),
     * next retries read from index 1..N.
     *
     * @return list<int>
     */
    public function scheduleInSeconds(): array
    {
        return config('payment.retry_schedule_seconds', [0, 300, 1800, 21600, 86400]);
    }

    public function maxRetries(): int
    {
        return count($this->scheduleInSeconds()) - 1;
    }

    public function nextDelaySeconds(int $retryCount): ?int
    {
        $schedule = $this->scheduleInSeconds();

        return $schedule[$retryCount + 1] ?? null;
    }
}
