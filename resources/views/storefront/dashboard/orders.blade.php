@extends('storefront.layout')

@section('content')
    <section class="mb-6">
        <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500">Dashboard</p>
        <h1 class="mt-1 text-3xl font-extrabold tracking-tight">Orders</h1>
    </section>

    @include('storefront.dashboard._nav')

    <section class="glass-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-5 py-3">ID</th>
                        <th class="px-5 py-3">Product</th>
                        <th class="px-5 py-3">Amount</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Payment Intent</th>
                        <th class="px-5 py-3">Fulfilled at</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr class="border-t border-slate-100">
                            <td class="px-5 py-3 font-semibold">#{{ $order->id }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $order->product?->name ?? '-' }}</td>
                            <td class="px-5 py-3">${{ number_format($order->amount / 100, 2) }} {{ $order->currency }}</td>
                            <td class="px-5 py-3">@include('storefront.dashboard._status', ['value' => $order->status])</td>
                            <td class="px-5 py-3 text-slate-600">#{{ $order->payment_intent_id }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ optional($order->fulfilled_at)->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-6 text-center text-slate-500">No finalized orders yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div class="mt-4">{{ $orders->links() }}</div>
@endsection
