@extends('storefront.layout')

@section('content')
    <section class="mb-6 flex items-end justify-between gap-4">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500">Storefront</p>
            <h1 class="mt-1 text-3xl font-extrabold tracking-tight">Products</h1>
        </div>
        <span class="rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-600">{{ $products->count() }} items</span>
    </section>

    <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse($products as $product)
            <article class="glass-card group p-5 transition hover:-translate-y-1">
                <div class="mb-4 h-28 rounded-xl bg-gradient-to-br from-emerald-100 via-cyan-100 to-blue-100"></div>
                <h2 class="text-lg font-bold tracking-tight">{{ $product->name }}</h2>
                <p class="mt-2 line-clamp-2 text-sm text-slate-600">{{ $product->description }}</p>
                <div class="mt-4 flex items-center justify-between">
                    <p class="text-xl font-extrabold">${{ number_format($product->price / 100, 2) }}</p>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('products.show', ['id' => $product->id]) }}" class="rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-bold text-white transition group-hover:bg-slate-700">View</a>
                        <a href="{{ route('checkout.show', ['product' => $product->id]) }}" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 transition hover:bg-slate-100">Buy</a>
                    </div>
                </div>
            </article>
        @empty
            <div class="glass-card col-span-full p-8 text-center text-slate-600">No products available.</div>
        @endforelse
    </section>
@endsection
