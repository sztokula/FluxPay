@extends('storefront.layout')

@section('content')
    <section class="mb-6">
        <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500">Dashboard</p>
        <h1 class="mt-1 text-3xl font-extrabold tracking-tight">Plans & Prices</h1>
    </section>

    @include('storefront.dashboard._nav')

    <section class="space-y-4">
        @forelse($plans as $plan)
            <article class="glass-card p-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-bold">{{ $plan->name }}</h2>
                        <p class="text-sm text-slate-600">{{ $plan->description ?: 'No description' }}</p>
                    </div>
                    @include('storefront.dashboard._status', ['value' => $plan->is_active ? 'active' : 'inactive'])
                </div>

                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="px-3 py-2">Price ID</th>
                                <th class="px-3 py-2">Amount</th>
                                <th class="px-3 py-2">Interval</th>
                                <th class="px-3 py-2">Trial days</th>
                                <th class="px-3 py-2">Active</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($plan->prices as $price)
                                <tr class="border-t border-slate-100">
                                    <td class="px-3 py-2 font-semibold">#{{ $price->id }}</td>
                                    <td class="px-3 py-2">${{ number_format($price->amount / 100, 2) }} {{ $price->currency }}</td>
                                    <td class="px-3 py-2">{{ $price->interval_count }} {{ $price->interval }}</td>
                                    <td class="px-3 py-2">{{ $price->trial_days }}</td>
                                    <td class="px-3 py-2">@include('storefront.dashboard._status', ['value' => $price->is_active ? 'yes' : 'no'])</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-3 py-4 text-center text-slate-500">No prices for this plan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>
        @empty
            <div class="glass-card p-8 text-center text-slate-500">No plans yet.</div>
        @endforelse
    </section>

    <div class="mt-4">{{ $plans->links() }}</div>
@endsection
