<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\EventLog;
use App\Models\Invoice;
use App\Models\LedgerEntry;
use App\Models\PaymentIntent;
use App\Models\Plan;
use App\Models\Order;
use App\Models\Payout;
use App\Models\Subscription;
use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StorefrontDashboardController extends Controller
{
    public function overview(): View
    {
        $customer = $this->demoCustomer();

        $paymentIntents = PaymentIntent::query()->where('customer_id', $customer->id);
        $invoices = Invoice::query()->where('customer_id', $customer->id);
        $subscriptions = Subscription::query()->where('customer_id', $customer->id);
        $payouts = Payout::query()->where('customer_id', $customer->id);
        $orders = $this->hasOrdersTable()
            ? Order::query()->where('customer_id', $customer->id)
            : null;

        $eventLogs = $this->eventLogsQuery($customer)->latest('happened_at')->limit(12)->get();

        return view('storefront.dashboard.overview', [
            'customer' => $customer,
            'stats' => [
                'payment_total' => (clone $paymentIntents)->count(),
                'payment_succeeded' => (clone $paymentIntents)->where('status', 'succeeded')->count(),
                'invoice_open' => (clone $invoices)->where('status', 'open')->count(),
                'subscription_active' => (clone $subscriptions)->where('status', 'active')->count(),
                'payout_paid' => (clone $payouts)->where('status', 'paid')->count(),
                'orders_paid' => $orders ? (clone $orders)->where('status', 'paid')->count() : 0,
            ],
            'eventLogs' => $eventLogs,
        ]);
    }

    public function payments(): View
    {
        $customer = $this->demoCustomer();

        $payments = PaymentIntent::query()
            ->where('customer_id', $customer->id)
            ->latest()
            ->paginate(15);

        return view('storefront.dashboard.payments', compact('payments'));
    }

    public function subscriptions(): View
    {
        $customer = $this->demoCustomer();

        $subscriptions = Subscription::query()
            ->with('price.plan')
            ->where('customer_id', $customer->id)
            ->latest()
            ->paginate(15);

        return view('storefront.dashboard.subscriptions', compact('subscriptions'));
    }

    public function invoices(): View
    {
        $customer = $this->demoCustomer();

        $invoices = Invoice::query()
            ->where('customer_id', $customer->id)
            ->latest()
            ->paginate(15);

        return view('storefront.dashboard.invoices', compact('invoices'));
    }

    public function payouts(): View
    {
        $customer = $this->demoCustomer();

        $payouts = Payout::query()
            ->where('customer_id', $customer->id)
            ->latest()
            ->paginate(15);

        return view('storefront.dashboard.payouts', compact('payouts'));
    }

    public function events(): View
    {
        $customer = $this->demoCustomer();

        $eventLogs = $this->eventLogsQuery($customer)
            ->latest('happened_at')
            ->paginate(20);

        return view('storefront.dashboard.events', compact('eventLogs'));
    }

    public function customers(): View
    {
        $customers = Customer::query()
            ->withCount(['paymentIntents', 'subscriptions', 'invoices', 'payouts'])
            ->latest()
            ->paginate(15);

        return view('storefront.dashboard.customers', compact('customers'));
    }

    public function ledger(): View
    {
        $ledgerEntries = LedgerEntry::query()
            ->with('customer')
            ->latest()
            ->paginate(20);

        return view('storefront.dashboard.ledger', compact('ledgerEntries'));
    }

    public function webhooks(): View
    {
        $endpoints = WebhookEndpoint::query()
            ->withCount('deliveries')
            ->latest()
            ->paginate(15);

        $recentDeliveries = WebhookDelivery::query()
            ->with('endpoint')
            ->latest()
            ->limit(20)
            ->get();

        return view('storefront.dashboard.webhooks', compact('endpoints', 'recentDeliveries'));
    }

    public function fraud(): View
    {
        $fraudCases = PaymentIntent::query()
            ->with('customer')
            ->whereIn('failure_code', ['fraud_review', 'fraud_blocked'])
            ->latest()
            ->paginate(20);

        return view('storefront.dashboard.fraud', compact('fraudCases'));
    }

    public function plans(): View
    {
        $plans = Plan::query()
            ->with(['prices' => fn ($priceQuery) => $priceQuery->orderBy('amount')])
            ->latest()
            ->paginate(15);

        return view('storefront.dashboard.plans', compact('plans'));
    }

    public function orders(): View
    {
        $customer = $this->demoCustomer();

        $orders = $this->hasOrdersTable()
            ? Order::query()
                ->with(['product', 'paymentIntent'])
                ->where('customer_id', $customer->id)
                ->latest()
                ->paginate(15)
            : new LengthAwarePaginator([], 0, 15);

        return view('storefront.dashboard.orders', compact('orders'));
    }

    public function system(): View
    {
        $jobStats = [
            'queued' => Schema::hasTable('jobs') ? DB::table('jobs')->count() : 0,
            'failed' => Schema::hasTable('failed_jobs') ? DB::table('failed_jobs')->count() : 0,
        ];

        $processingStats = [
            'processing_total' => PaymentIntent::query()
                ->where('status', 'processing')
                ->count(),
            'stale_without_retry' => PaymentIntent::query()
                ->where('status', 'processing')
                ->whereNull('next_retry_at')
                ->where('confirmed_at', '<=', now()->subMinutes((int) config('payment.processing_stale_no_retry_minutes', 2)))
                ->count(),
            'overdue_retry' => PaymentIntent::query()
                ->where('status', 'processing')
                ->whereNotNull('next_retry_at')
                ->where('next_retry_at', '<=', now()->subMinutes((int) config('payment.processing_retry_grace_minutes', 5)))
                ->count(),
        ];

        $oldestQueuedJob = Schema::hasTable('jobs')
            ? DB::table('jobs')->orderBy('available_at')->first()
            : null;

        $latestFailedJob = Schema::hasTable('failed_jobs')
            ? DB::table('failed_jobs')->latest('failed_at')->first()
            : null;

        $workerLastJobAtRaw = Cache::get('system.worker_last_job_at');
        $schedulerLastTickAtRaw = Cache::get('system.scheduler_last_tick_at');
        $watchdogLastRunAtRaw = Cache::get('system.watchdog_last_run_at');

        $workerLastJobAt = is_string($workerLastJobAtRaw) ? Carbon::parse($workerLastJobAtRaw) : null;
        $schedulerLastTickAt = is_string($schedulerLastTickAtRaw) ? Carbon::parse($schedulerLastTickAtRaw) : null;
        $watchdogLastRunAt = is_string($watchdogLastRunAtRaw) ? Carbon::parse($watchdogLastRunAtRaw) : null;

        $workerNeedsRecentHeartbeat = $jobStats['queued'] > 0
            || $processingStats['processing_total'] > 0
            || $processingStats['overdue_retry'] > 0;

        $health = [
            'worker_recent' => ! $workerNeedsRecentHeartbeat
                || ($workerLastJobAt?->greaterThanOrEqualTo(now()->subMinutes(5)) ?? false),
            'scheduler_recent' => $schedulerLastTickAt?->greaterThanOrEqualTo(now()->subMinutes(2)) ?? false,
            'watchdog_recent' => $watchdogLastRunAt?->greaterThanOrEqualTo(now()->subMinutes(2)) ?? false,
            'worker_needs_recent_heartbeat' => $workerNeedsRecentHeartbeat,
        ];

        return view('storefront.dashboard.system', compact(
            'jobStats',
            'processingStats',
            'oldestQueuedJob',
            'latestFailedJob',
            'workerLastJobAt',
            'schedulerLastTickAt',
            'watchdogLastRunAt',
            'health'
        ));
    }

    private function demoCustomer(): Customer
    {
        return Customer::query()->firstOrCreate(
            ['email' => 'demo@local.test'],
            ['name' => 'Demo Customer']
        );
    }

    private function eventLogsQuery(Customer $customer): Builder
    {
        $paymentIntentIds = PaymentIntent::query()
            ->where('customer_id', $customer->id)
            ->pluck('id');

        $invoiceIds = Invoice::query()
            ->where('customer_id', $customer->id)
            ->pluck('id');

        $subscriptionIds = Subscription::query()
            ->where('customer_id', $customer->id)
            ->pluck('id');

        $payoutIds = Payout::query()
            ->where('customer_id', $customer->id)
            ->pluck('id');

        $orderIds = $this->hasOrdersTable()
            ? Order::query()->where('customer_id', $customer->id)->pluck('id')
            : collect();

        return EventLog::query()->where(function ($query) use ($customer, $paymentIntentIds, $invoiceIds, $subscriptionIds, $payoutIds, $orderIds): void {
            if ($customer->user_id !== null) {
                $query->orWhere('user_id', $customer->user_id);
            }

            $query->orWhere(fn ($paymentQuery) => $paymentQuery
                ->where('aggregate_type', 'payment_intent')
                ->whereIn('aggregate_id', $paymentIntentIds));

            $query->orWhere(fn ($invoiceQuery) => $invoiceQuery
                ->where('aggregate_type', 'invoice')
                ->whereIn('aggregate_id', $invoiceIds));

            $query->orWhere(fn ($subscriptionQuery) => $subscriptionQuery
                ->where('aggregate_type', 'subscription')
                ->whereIn('aggregate_id', $subscriptionIds));

            $query->orWhere(fn ($payoutQuery) => $payoutQuery
                ->where('aggregate_type', 'payout')
                ->whereIn('aggregate_id', $payoutIds));

            $query->orWhere(fn ($orderQuery) => $orderQuery
                ->where('aggregate_type', 'order')
                ->whereIn('aggregate_id', $orderIds));
        });
    }

    private function hasOrdersTable(): bool
    {
        return Schema::hasTable('orders');
    }
}
