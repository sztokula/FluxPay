@extends('storefront.layout')

@section('content')
    <section class="grid gap-6 lg:grid-cols-[1.1fr,0.9fr] lg:items-center">
        <div class="glass-card p-8 sm:p-10">
            <p class="text-xs font-bold uppercase tracking-[0.22em] text-emerald-600">FluxPay local payment simulator</p>
            <h1 class="mt-3 text-4xl font-extrabold leading-tight tracking-tight sm:text-5xl">
                Build and test payment flows
                <span class="bg-gradient-to-r from-emerald-600 to-blue-600 bg-clip-text text-transparent">without real gateways</span>
            </h1>
            <p class="mt-4 max-w-2xl text-base leading-relaxed text-slate-600">
                End-to-end playground for intents, retries, subscriptions, invoices, webhook delivery and ledger consistency.
            </p>

            <div class="mt-7 flex flex-wrap gap-3">
                <a href="{{ route('products.index') }}" class="action-btn">Browse storefront</a>
                @auth
                    <a href="{{ route('dashboard.overview') }}" class="action-btn-secondary">Open dashboard</a>
                    <a href="{{ route('api.reference') }}" class="action-btn-secondary">Open API</a>
                @else
                    <a href="{{ route('auth.login.form') }}" class="action-btn-secondary">Login to dashboard</a>
                    <a href="{{ route('auth.register.form') }}" class="action-btn-secondary">Create account</a>
                @endauth
            </div>
        </div>

        <aside class="glass-card p-6 sm:p-8">
            <h2 class="text-lg font-bold">Test Cards</h2>
            <ul class="mt-4 space-y-3 text-sm">
                <li class="flex items-center justify-between gap-3 rounded-xl bg-slate-50 p-3">
                    <span><span class="font-semibold">4242 4242 4242 4242</span> - success</span>
                    <button type="button" class="action-btn-secondary copy-card-btn !px-3 !py-1.5 !text-xs" data-card="4242424242424242">Copy</button>
                </li>
                <li class="flex items-center justify-between gap-3 rounded-xl bg-slate-50 p-3">
                    <span><span class="font-semibold">4000 0000 0000 0002</span> - insufficient funds</span>
                    <button type="button" class="action-btn-secondary copy-card-btn !px-3 !py-1.5 !text-xs" data-card="4000000000000002">Copy</button>
                </li>
                <li class="flex items-center justify-between gap-3 rounded-xl bg-slate-50 p-3">
                    <span><span class="font-semibold">4000 0000 0000 9995</span> - temporary failure</span>
                    <button type="button" class="action-btn-secondary copy-card-btn !px-3 !py-1.5 !text-xs" data-card="4000000000009995">Copy</button>
                </li>
                <li class="flex items-center justify-between gap-3 rounded-xl bg-slate-50 p-3">
                    <span><span class="font-semibold">4000 0000 0000 3063</span> - requires action</span>
                    <button type="button" class="action-btn-secondary copy-card-btn !px-3 !py-1.5 !text-xs" data-card="4000000000003063">Copy</button>
                </li>
            </ul>
            <p id="copy-card-status" class="mt-3 text-xs font-semibold text-slate-500"></p>
        </aside>
    </section>

    <script>
        (() => {
            const status = document.getElementById('copy-card-status');
            const copyButtons = document.querySelectorAll('.copy-card-btn');

            copyButtons.forEach((button) => {
                button.addEventListener('click', async () => {
                    const card = button.dataset.card || '';
                    if (!card) {
                        return;
                    }

                    try {
                        await navigator.clipboard.writeText(card);
                        if (status) {
                            status.textContent = 'Copied test card: ' + card;
                        }
                    } catch (error) {
                        if (status) {
                            status.textContent = 'Could not copy automatically. Card: ' + card;
                        }
                    }
                });
            });
        })();
    </script>
@endsection
