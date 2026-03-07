@extends('storefront.layout')

@section('content')
    <section class="mx-auto max-w-3xl">
        @php
            $defaultTestCard = app(\App\Services\AppSettingsService::class)->get('checkout_default_test_card', '4242424242424242');
            $pollIntervalMs = (int) app(\App\Services\AppSettingsService::class)->get('payment_live_poll_interval_ms', 2000);
        @endphp
        <div class="glass-card p-8 sm:p-10">
            <a href="{{ route('products.index') }}" class="text-xs font-semibold text-slate-500 underline">Back to storefront</a>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h1 class="text-2xl font-extrabold tracking-tight">Payment #{{ $intent->id }}</h1>
                @php
                    $status = $intent->status->value;
                    $statusClasses = match ($status) {
                        'succeeded' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                        'failed', 'canceled' => 'border-red-200 bg-red-50 text-red-700',
                        'processing' => 'border-amber-200 bg-amber-50 text-amber-700',
                        default => 'border-slate-200 bg-slate-50 text-slate-600',
                    };
                    $dotClasses = match ($status) {
                        'succeeded' => 'bg-emerald-500',
                        'failed', 'canceled' => 'bg-red-500',
                        'processing' => 'bg-amber-500',
                        default => 'bg-slate-400',
                    };
                @endphp
                <span class="status-pill {{ $statusClasses }}">
                    <span class="status-dot {{ $dotClasses }} {{ $status === 'processing' ? 'live-pulse' : '' }}"></span>
                    {{ str_replace('_', ' ', $status) }}
                </span>
            </div>
            <p class="mt-2 text-slate-600">Amount: <span class="font-bold">${{ number_format($intent->amount / 100, 2) }} {{ $intent->currency }}</span></p>
            <p class="mt-1 text-xs text-slate-500">Stay on this page until redirect to success/failure.</p>

            @if(in_array($intent->status->value, ['requires_confirmation', 'requires_payment_method', 'requires_action']))
                <form action="{{ route('payment.confirm', ['intent' => $intent->id]) }}" method="POST" class="mt-7 space-y-4">
                    @csrf
                    <div>
                        <label for="card_number" class="block text-sm font-semibold text-slate-700">Test card number</label>
                        <input id="card_number" name="card_number" value="{{ $defaultTestCard }}" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 font-medium tracking-wide focus:border-slate-600 focus:outline-none" required>
                        @error('card_number')
                            <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-xs leading-relaxed text-slate-600">
                        <p class="font-semibold text-slate-700">Quick test cards</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <button type="button" class="action-btn-secondary !px-3 !py-1.5 !text-xs card-preset-btn" data-card="4242424242424242">4242 success</button>
                            <button type="button" class="action-btn-secondary !px-3 !py-1.5 !text-xs card-preset-btn" data-card="4000000000000002">0002 insufficient</button>
                            <button type="button" class="action-btn-secondary !px-3 !py-1.5 !text-xs card-preset-btn" data-card="4000000000009995">9995 temporary</button>
                            <button type="button" class="action-btn-secondary !px-3 !py-1.5 !text-xs card-preset-btn" data-card="4000000000003063">3063 action</button>
                        </div>
                    </div>

                    <button id="confirm-payment-btn" class="action-btn w-full sm:w-auto">Confirm payment</button>
                </form>
            @elseif($intent->status->value === 'succeeded')
                <a href="{{ route('payment.success', ['intent' => $intent->id]) }}" class="action-btn mt-7">Finish order</a>
            @elseif($intent->status->value === 'failed')
                <a href="{{ route('payment.failed', ['intent' => $intent->id]) }}" class="action-btn mt-7 bg-red-600 hover:bg-red-500">See failure</a>
            @else
                <div id="payment-live-status" class="mt-7 rounded-xl bg-amber-50 p-4 text-sm font-medium text-amber-700">
                    <p id="payment-live-message" class="flex items-center gap-2">
                        <span class="status-dot bg-amber-500 live-pulse"></span>
                        Checking your payment live...
                    </p>
                    <p id="payment-live-hint" class="mt-2 text-xs text-amber-700/80">You will be redirected automatically when status changes.</p>
                    @if($intent->next_retry_at)
                        <p class="mt-1 text-xs text-amber-700/80">Next retry at: {{ $intent->next_retry_at->format('Y-m-d H:i:s') }}</p>
                    @endif
                </div>
            @endif
        </div>
    </section>

    @if(in_array($intent->status->value, ['processing', 'requires_confirmation', 'requires_payment_method', 'requires_action']))
        <script>
            (() => {
                const cardInput = document.getElementById('card_number');
                const presetButtons = document.querySelectorAll('.card-preset-btn');
                const confirmButton = document.getElementById('confirm-payment-btn');
                const paymentForm = cardInput ? cardInput.closest('form') : null;

                presetButtons.forEach((button) => {
                    button.addEventListener('click', () => {
                        if (cardInput) {
                            cardInput.value = button.dataset.card || '';
                            cardInput.focus();
                        }
                    });
                });

                if (paymentForm && confirmButton) {
                    paymentForm.addEventListener('submit', () => {
                        confirmButton.setAttribute('disabled', 'disabled');
                        confirmButton.classList.add('opacity-70', 'cursor-not-allowed');
                        confirmButton.textContent = 'Submitting...';
                    });
                }

                if (@json($intent->status->value) !== 'processing') {
                    return;
                }

                const statusUrl = @json(route('payment.status', ['intent' => $intent->id]));
                const liveMessage = document.getElementById('payment-live-message');
                const liveHint = document.getElementById('payment-live-hint');
                const pollIntervalMs = Math.max(500, Number(@json($pollIntervalMs)) || 2000);
                const maxAutoPollingMs = 90000;
                let elapsedSeconds = 0;

                const poll = async () => {
                    elapsedSeconds += Math.round(pollIntervalMs / 1000);
                    try {
                        const response = await fetch(statusUrl, { headers: { 'Accept': 'application/json' } });
                        if (!response.ok) {
                            return;
                        }

                        const data = await response.json();

                        if (data.redirect_url) {
                            window.location.href = data.redirect_url;
                            return;
                        }

                        if (data.status === 'requires_action') {
                            window.location.reload();
                            return;
                        }

                        if (liveMessage) {
                            liveMessage.innerHTML = '<span class="status-dot bg-amber-500 live-pulse"></span>Still processing... ' + elapsedSeconds + 's';
                        }

                        if (liveHint && data.next_retry_at) {
                            const retryAt = new Date(data.next_retry_at);
                            if (!Number.isNaN(retryAt.getTime())) {
                                liveHint.innerHTML = 'Retry #' + (data.retry_count ?? 0) + ' scheduled for ' + retryAt.toLocaleTimeString() + '.';
                            }
                        }

                        if (liveHint && elapsedSeconds >= 20) {
                            liveHint.innerHTML = 'Long processing detected. Ensure queue worker is running: <code class="rounded bg-amber-100 px-1 py-0.5">php artisan queue:work</code>';
                        }

                        if (liveHint && elapsedSeconds * 1000 >= maxAutoPollingMs) {
                            liveHint.innerHTML = 'Still processing after 90s. Keep this page open and run <code class="rounded bg-amber-100 px-1 py-0.5">php artisan queue:work</code>, then refresh.';
                        }
                    } catch (error) {
                        if (liveMessage) {
                            liveMessage.innerHTML = '<span class="status-dot bg-red-500"></span>Temporary connection issue. Retrying...';
                        }
                    }
                };

                poll();
                setInterval(poll, pollIntervalMs);
            })();
        </script>
    @endif
@endsection
