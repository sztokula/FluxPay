@extends('storefront.layout')

@section('content')
    <section class="mb-6">
        <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500">Dashboard</p>
        <h1 class="mt-1 text-3xl font-extrabold tracking-tight">Payouts</h1>
    </section>

    @include('storefront.dashboard._nav')

    <section class="glass-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-5 py-3">ID</th>
                        <th class="px-5 py-3">Amount</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Failure</th>
                        <th class="px-5 py-3">Processed</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payouts as $payout)
                        <tr class="border-t border-slate-100">
                            <td class="px-5 py-3 font-semibold">#{{ $payout->id }}</td>
                            <td class="px-5 py-3">${{ number_format($payout->amount / 100, 2) }} {{ $payout->currency }}</td>
                            <td class="px-5 py-3">@include('storefront.dashboard._status', ['value' => $payout->status->value])</td>
                            <td class="px-5 py-3 text-slate-500">{{ $payout->failure_reason ?? '-' }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ optional($payout->processed_at)->format('Y-m-d H:i') ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-6 text-center text-slate-500">No payouts yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div class="mt-4">{{ $payouts->links() }}</div>
@endsection
