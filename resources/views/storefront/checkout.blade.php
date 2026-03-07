@extends('storefront.layout')

@section('content')
    <section class="mx-auto max-w-3xl">
        <div class="glass-card p-8 sm:p-10">
            <a href="{{ route('products.show', ['id' => $product->id]) }}" class="text-xs font-semibold text-slate-500 underline">Back to product</a>
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500">Checkout</p>
            <h1 class="mt-2 text-3xl font-extrabold tracking-tight">{{ $product->name }}</h1>
            <p class="mt-2 text-slate-600">{{ $product->description }}</p>

            <div class="mt-6 grid gap-2 rounded-xl border border-slate-200 bg-slate-50 p-4 text-xs font-semibold text-slate-600 sm:grid-cols-3">
                <p><span class="text-slate-900">1.</span> Intent created</p>
                <p><span class="text-slate-900">2.</span> Card confirmation</p>
                <p><span class="text-slate-900">3.</span> Live status + result</p>
            </div>

            <div class="mt-6 flex items-center justify-between rounded-xl bg-slate-50 p-4">
                <span class="text-sm font-semibold text-slate-600">Total</span>
                <span class="text-2xl font-black">${{ number_format($product->price / 100, 2) }}</span>
            </div>

            <div class="mt-7 flex flex-wrap gap-3">
                <a href="{{ route('payment.show', ['intent' => $paymentIntent->id]) }}" class="action-btn w-full sm:w-auto">Proceed to payment</a>
                <a href="{{ route('products.show', ['id' => $product->id]) }}" class="action-btn-secondary w-full sm:w-auto">Edit selection</a>
            </div>
        </div>
    </section>
@endsection
