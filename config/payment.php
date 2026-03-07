<?php

return [
    'api_rate_limit_per_minute' => 240,
    'processing_stale_no_retry_minutes' => 2,
    'processing_retry_grace_minutes' => 5,
    'retry_schedule_seconds' => [0, 300, 1800, 21600, 86400],
    'fraud' => [
        'blacklisted_cards' => ['4000000000000341'],
        'attempts_window_minutes' => 15,
        'attempts_threshold' => 6,
        'failed_attempts_window_minutes' => 60,
        'failed_attempts_threshold' => 3,
        'high_value_without_history_amount' => 100000,
    ],
    'webhooks' => [
        'supported_events' => [
            'payment_intent.created',
            'payment_intent.succeeded',
            'payment_intent.failed',
            'invoice.created',
            'invoice.paid',
            'subscription.created',
            'subscription.canceled',
            'payout.created',
            'payout.failed',
        ],
        'retry_schedule_seconds' => [60, 300, 1800, 21600, 86400],
    ],
];
