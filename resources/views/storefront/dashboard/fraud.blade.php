@extends('storefront.layout')

@section('content')
    <section class="mb-6">
        <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500">Dashboard</p>
        <h1 class="mt-1 text-3xl font-extrabold tracking-tight">Fraud Cases</h1>
    </section>

    @include('storefront.dashboard._nav')

    <section class="glass-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Payment</th>
                        <th class="px-5 py-3">Customer</th>
                        <th class="px-5 py-3">Amount</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Failure code</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($fraudCases as $case)
                        <tr class="border-t border-slate-100">
                            <td class="px-5 py-3 font-semibold">#{{ $case->id }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $case->customer?->email ?? '-' }}</td>
                            <td class="px-5 py-3">${{ number_format($case->amount / 100, 2) }} {{ $case->currency }}</td>
                            <td class="px-5 py-3">@include('storefront.dashboard._status', ['value' => $case->status->value])</td>
                            <td class="px-5 py-3 text-slate-500">{{ $case->failure_code }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-6 text-center text-slate-500">No fraud-review cases yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div class="mt-4">{{ $fraudCases->links() }}</div>
@endsection
