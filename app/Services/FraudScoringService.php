<?php

namespace App\Services;

use App\Enums\FraudDecision;
use App\Models\Customer;

class FraudScoringService
{
    public function __construct(private AppSettingsService $appSettingsService)
    {
    }

    public function score(Customer $customer, int $amount, string $cardNumber): FraudDecision
    {
        $blacklistedCards = config('payment.fraud.blacklisted_cards', []);

        if (in_array($cardNumber, $blacklistedCards, true)) {
            return FraudDecision::Block;
        }

        $attemptsWindowMinutes = (int) config('payment.fraud.attempts_window_minutes', 15);
        $attemptsThreshold = (int) config('payment.fraud.attempts_threshold', 6);

        $recentAttempts = $customer->paymentIntents()
            ->where('created_at', '>=', now()->subMinutes($attemptsWindowMinutes))
            ->count();

        if ($recentAttempts >= $attemptsThreshold) {
            return FraudDecision::Review;
        }

        $windowMinutes = (int) config('payment.fraud.failed_attempts_window_minutes', 60);
        $failedThreshold = (int) config('payment.fraud.failed_attempts_threshold', 3);

        $failedAttempts = $customer->paymentIntents()
            ->where('status', 'failed')
            ->where('created_at', '>=', now()->subMinutes($windowMinutes))
            ->count();

        if ($failedAttempts >= $failedThreshold) {
            return FraudDecision::Review;
        }

        $highValueWithoutHistoryAmount = (int) $this->appSettingsService->get(
            'high_value_review_threshold',
            (int) config('payment.fraud.high_value_without_history_amount', 100000)
        );

        $historicalSuccessfulPayments = $customer->paymentIntents()
            ->where('status', 'succeeded')
            ->count();

        if ($historicalSuccessfulPayments === 0 && $amount >= $highValueWithoutHistoryAmount) {
            return FraudDecision::Review;
        }

        return FraudDecision::Allow;
    }
}
