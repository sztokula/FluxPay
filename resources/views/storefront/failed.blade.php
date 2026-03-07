@extends('storefront.layout')

@section('content')
    <section class="mx-auto max-w-2xl text-center">
        <div class="glass-card p-10">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-red-100 text-red-700">
                <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M12 8V13M12 16.5V16.6M10.29 3.86L1.82 18A2 2 0 003.53 21H20.47A2 2 0 0022.18 18L13.71 3.86A2 2 0 0010.29 3.86Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>
            <h1 class="mt-5 text-3xl font-extrabold tracking-tight text-red-700">Payment failed</h1>
            <p class="mt-3 text-slate-600">The intent ended in failed state. You can retry with another test card.</p>

            @if($intent)
                <div class="mx-auto mt-6 max-w-md rounded-xl border border-red-200 bg-red-50 p-4 text-left text-sm text-red-900">
                    <p><span class="font-semibold">Payment intent:</span> #{{ $intent->id }}</p>
                    <p class="mt-1"><span class="font-semibold">Failure code:</span> {{ $intent->failure_code ?? 'unknown' }}</p>
                    <p class="mt-1"><span class="font-semibold">Message:</span> {{ $intent->failure_message ?? 'No details' }}</p>
                </div>
            @endif

            <div class="mt-7 flex flex-wrap justify-center gap-3">
                @if($intent)
                    <a href="{{ route('payment.show', ['intent' => $intent->id]) }}" class="action-btn">Try again</a>
                @endif
                <a href="{{ route('products.index') }}" class="action-btn-secondary">Back to products</a>
                <a href="{{ route('home') }}" class="action-btn bg-red-600 hover:bg-red-500">Go home</a>
            </div>
        </div>
    </section>
@endsection
