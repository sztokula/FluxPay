@extends('storefront.layout')

@section('content')
    <section class="mx-auto max-w-2xl text-center">
        <div class="glass-card p-10">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M20 7L10 17L4 11" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>
            <h1 class="mt-5 text-3xl font-extrabold tracking-tight text-emerald-700">Payment succeeded</h1>
            <p class="mt-3 text-slate-600">Order finalized and events queued successfully.</p>

            @if($intent)
                <div class="mx-auto mt-6 max-w-md rounded-xl border border-slate-200 bg-slate-50 p-4 text-left text-sm">
                    <p><span class="font-semibold">Payment intent:</span> #{{ $intent->id }}</p>
                    <p class="mt-1"><span class="font-semibold">Amount:</span> ${{ number_format($intent->amount / 100, 2) }} {{ $intent->currency }}</p>
                    @if($intent->order)
                        <p class="mt-1"><span class="font-semibold">Order:</span> #{{ $intent->order->id }}</p>
                        <p class="mt-1"><span class="font-semibold">Product:</span> {{ $intent->order->product?->name ?? 'N/A' }}</p>
                    @endif
                </div>
            @endif

            <a href="{{ route('products.index') }}" class="action-btn mt-7">Back to products</a>
        </div>
    </section>
@endsection
