<?php

use App\Enums\SubscriptionStatus;
use App\Jobs\RenewSubscription;
use App\Models\Subscription;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function (): void {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function (): void {
    Cache::put('system.scheduler_last_tick_at', now()->toIso8601String(), now()->addDay());

    Subscription::query()
        ->whereIn('status', [SubscriptionStatus::Active, SubscriptionStatus::Trialing])
        ->where('current_period_end', '<=', now())
        ->limit(50)
        ->get()
        ->each(fn (Subscription $subscription) => RenewSubscription::dispatch($subscription->id));
})->everyMinute();

Schedule::command('payment-intents:mark-stale-processing')->everyMinute();
