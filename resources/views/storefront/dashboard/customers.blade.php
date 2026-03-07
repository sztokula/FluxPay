@extends('storefront.layout')

@section('content')
    <section class="mb-6">
        <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500">Dashboard</p>
        <h1 class="mt-1 text-3xl font-extrabold tracking-tight">Customers</h1>
    </section>

    @include('storefront.dashboard._nav')

    <section class="glass-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-5 py-3">ID</th>
                        <th class="px-5 py-3">Email</th>
                        <th class="px-5 py-3">Name</th>
                        <th class="px-5 py-3">Payments</th>
                        <th class="px-5 py-3">Subscriptions</th>
                        <th class="px-5 py-3">Invoices</th>
                        <th class="px-5 py-3">Payouts</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                        <tr class="border-t border-slate-100">
                            <td class="px-5 py-3 font-semibold">#{{ $customer->id }}</td>
                            <td class="px-5 py-3">{{ $customer->email }}</td>
                            <td class="px-5 py-3">{{ $customer->name }}</td>
                            <td class="px-5 py-3">{{ $customer->payment_intents_count }}</td>
                            <td class="px-5 py-3">{{ $customer->subscriptions_count }}</td>
                            <td class="px-5 py-3">{{ $customer->invoices_count }}</td>
                            <td class="px-5 py-3">{{ $customer->payouts_count }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-6 text-center text-slate-500">
                                No customers yet.
                                <a href="{{ route('products.index') }}" class="ml-2 font-semibold text-slate-700 underline">Start first checkout</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div class="mt-4">{{ $customers->links() }}</div>
@endsection
