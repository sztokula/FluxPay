@extends('storefront.layout')

@section('content')
    <section class="mb-6">
        <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500">Stripe-style dashboard</p>
        <h1 class="mt-1 text-3xl font-extrabold tracking-tight">Operations Overview</h1>
        <p class="mt-2 text-sm text-slate-600">Customer: {{ $customer->email }}</p>
        <div class="mt-4 flex flex-wrap gap-2">
            <a href="{{ route('products.index') }}" class="action-btn-secondary">Run new checkout</a>
            <a href="{{ route('dashboard.payments') }}" class="action-btn-secondary">Inspect payments</a>
            <a href="{{ route('dashboard.events') }}" class="action-btn-secondary">Open event log</a>
        </div>
    </section>

    @include('storefront.dashboard._nav')

    <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-6">
        <article class="glass-card p-4">
            <p class="text-xs font-semibold uppercase text-slate-500">Payments</p>
            <p class="mt-2 text-2xl font-black">{{ $stats['payment_total'] }}</p>
        </article>
        <article class="glass-card p-4">
            <p class="text-xs font-semibold uppercase text-slate-500">Succeeded</p>
            <p class="mt-2 text-2xl font-black text-emerald-600">{{ $stats['payment_succeeded'] }}</p>
        </article>
        <article class="glass-card p-4">
            <p class="text-xs font-semibold uppercase text-slate-500">Open invoices</p>
            <p class="mt-2 text-2xl font-black">{{ $stats['invoice_open'] }}</p>
        </article>
        <article class="glass-card p-4">
            <p class="text-xs font-semibold uppercase text-slate-500">Active subs</p>
            <p class="mt-2 text-2xl font-black">{{ $stats['subscription_active'] }}</p>
        </article>
        <article class="glass-card p-4">
            <p class="text-xs font-semibold uppercase text-slate-500">Paid payouts</p>
            <p class="mt-2 text-2xl font-black">{{ $stats['payout_paid'] }}</p>
        </article>
        <article class="glass-card p-4">
            <p class="text-xs font-semibold uppercase text-slate-500">Paid orders</p>
            <p class="mt-2 text-2xl font-black">{{ $stats['orders_paid'] }}</p>
        </article>
    </section>

    <section class="mt-6 glass-card overflow-hidden">
        <div class="border-b border-slate-200/70 px-5 py-4">
            <h2 class="font-bold">Recent events</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Event</th>
                        <th class="px-5 py-3">Aggregate</th>
                        <th class="px-5 py-3">At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($eventLogs as $event)
                        <tr class="border-t border-slate-100">
                            <td class="px-5 py-3 font-semibold text-slate-800">{{ $event->event_name }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $event->aggregate_type }} #{{ $event->aggregate_id }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ optional($event->happened_at)->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-5 py-6 text-center text-slate-500">No events yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
