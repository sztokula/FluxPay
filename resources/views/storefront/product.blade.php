@extends('storefront.layout')

@section('content')
    <section class="grid gap-6 lg:grid-cols-[1fr,0.8fr] lg:items-start">
        <article class="glass-card p-8">
            <a href="{{ route('products.index') }}" class="text-xs font-semibold text-slate-500 underline">Back to products</a>
            <div class="mb-6 h-44 rounded-2xl bg-gradient-to-br from-emerald-100 via-cyan-100 to-blue-100"></div>
            <h1 class="text-3xl font-extrabold tracking-tight">{{ $product->name }}</h1>
            <p class="mt-3 text-slate-600">{{ $product->description }}</p>
            <p class="mt-6 text-4xl font-black">${{ number_format($product->price / 100, 2) }}</p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('checkout.show', ['product' => $product->id]) }}" class="action-btn">Buy now</a>
                <a href="{{ route('products.index') }}" class="action-btn-secondary">Continue browsing</a>
            </div>
        </article>

        <aside class="glass-card p-6">
            <h2 class="text-lg font-bold">What happens next</h2>
            <ol class="mt-4 space-y-3 text-sm text-slate-600">
                <li>1. System creates a payment intent.</li>
                <li>2. You provide a deterministic test card.</li>
                <li>3. Intent is processed with retries/webhooks.</li>
                <li>4. Order lands on success or failure screen.</li>
            </ol>
        </aside>
    </section>
@endsection
