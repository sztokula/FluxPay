@extends('storefront.layout')

@section('content')
    <section class="mb-6">
        <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500">Dashboard</p>
        <h1 class="mt-1 text-3xl font-extrabold tracking-tight">Ledger</h1>
    </section>

    @include('storefront.dashboard._nav')

    <section class="glass-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-5 py-3">ID</th>
                        <th class="px-5 py-3">Customer</th>
                        <th class="px-5 py-3">Type</th>
                        <th class="px-5 py-3">Direction</th>
                        <th class="px-5 py-3">Amount</th>
                        <th class="px-5 py-3">Reference</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ledgerEntries as $entry)
                        <tr class="border-t border-slate-100">
                            <td class="px-5 py-3 font-semibold">#{{ $entry->id }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $entry->customer?->email ?? '-' }}</td>
                            <td class="px-5 py-3">@include('storefront.dashboard._status', ['value' => $entry->type->value])</td>
                            <td class="px-5 py-3">@include('storefront.dashboard._status', ['value' => $entry->direction])</td>
                            <td class="px-5 py-3">${{ number_format($entry->amount / 100, 2) }} {{ $entry->currency }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ $entry->reference_type }} #{{ $entry->reference_id }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-6 text-center text-slate-500">
                                No ledger entries yet.
                                <a href="{{ route('products.index') }}" class="ml-2 font-semibold text-slate-700 underline">Create first payment</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div class="mt-4">{{ $ledgerEntries->links() }}</div>
@endsection
