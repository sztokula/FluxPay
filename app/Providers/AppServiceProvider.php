<?php

namespace App\Providers;

use App\Services\AppSettingsService;
use App\Models\Customer;
use App\Models\EventLog;
use App\Models\Invoice;
use App\Models\LedgerEntry;
use App\Models\PaymentIntent;
use App\Models\Payout;
use App\Models\Subscription;
use App\Models\WebhookEndpoint;
use App\Policies\CustomerPolicy;
use App\Policies\EventLogPolicy;
use App\Policies\InvoicePolicy;
use App\Policies\LedgerEntryPolicy;
use App\Policies\PaymentIntentPolicy;
use App\Policies\PayoutPolicy;
use App\Policies\SubscriptionPolicy;
use App\Policies\WebhookEndpointPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request): Limit {
            $settingsService = app(AppSettingsService::class);
            $perMinute = (int) $settingsService->get(
                'api_rate_limit_per_minute',
                (int) config('payment.api_rate_limit_per_minute', 240)
            );

            $key = $request->user()?->id
                ? 'user:'.$request->user()->id
                : 'ip:'.$request->ip();

            return Limit::perMinute($perMinute)
                ->by($key);
        });

        Gate::policy(Customer::class, CustomerPolicy::class);
        Gate::policy(PaymentIntent::class, PaymentIntentPolicy::class);
        Gate::policy(Subscription::class, SubscriptionPolicy::class);
        Gate::policy(WebhookEndpoint::class, WebhookEndpointPolicy::class);
        Gate::policy(Invoice::class, InvoicePolicy::class);
        Gate::policy(Payout::class, PayoutPolicy::class);
        Gate::policy(LedgerEntry::class, LedgerEntryPolicy::class);
        Gate::policy(EventLog::class, EventLogPolicy::class);
    }
}
