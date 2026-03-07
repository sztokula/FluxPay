@extends('storefront.layout')

@section('content')
    <section class="mb-6">
        <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500">Dashboard</p>
        <h1 class="mt-1 text-3xl font-extrabold tracking-tight">Payments</h1>
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
                        <th class="px-5 py-3">Created</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr class="border-t border-slate-100">
                            <td class="px-5 py-3 font-semibold">#{{ $payment->id }}</td>
                            <td class="px-5 py-3">${{ number_format($payment->amount / 100, 2) }} {{ $payment->currency }}</td>
                            <td class="px-5 py-3">@include('storefront.dashboard._status', ['value' => $payment->status->value])</td>
                            <td class="px-5 py-3 text-slate-500">{{ $payment->failure_code ?? '-' }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ $payment->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-6 text-center text-slate-500">
                                No payments yet.
                                <a href="{{ route('products.index') }}" class="ml-2 font-semibold text-slate-700 underline">Go to storefront</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div class="mt-4">{{ $payments->links() }}</div>
@endsection
