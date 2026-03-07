@extends('storefront.layout')

@section('content')
    <section class="mb-6">
        <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500">Dashboard</p>
        <h1 class="mt-1 text-3xl font-extrabold tracking-tight">Subscriptions</h1>
    </section>

    @include('storefront.dashboard._nav')

    <section class="glass-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-5 py-3">ID</th>
                        <th class="px-5 py-3">Plan</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Period End</th>
                        <th class="px-5 py-3">Created</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subscriptions as $subscription)
                        <tr class="border-t border-slate-100">
                            <td class="px-5 py-3 font-semibold">#{{ $subscription->id }}</td>
                            <td class="px-5 py-3">
                                {{ $subscription->price?->plan?->name ?? 'Unknown' }}
                            </td>
                            <td class="px-5 py-3">@include('storefront.dashboard._status', ['value' => $subscription->status->value])</td>
                            <td class="px-5 py-3 text-slate-500">{{ optional($subscription->current_period_end)->format('Y-m-d') }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ $subscription->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-6 text-center text-slate-500">No subscriptions yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div class="mt-4">{{ $subscriptions->links() }}</div>
@endsection
