@extends('storefront.layout')

@section('content')
    <section class="mb-6">
        <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500">Dashboard</p>
        <h1 class="mt-1 text-3xl font-extrabold tracking-tight">Settings & Functions</h1>
        <p class="mt-2 text-sm text-slate-600">Project-level settings and operational test actions.</p>
    </section>

    @include('storefront.dashboard._nav')

    @if(session('settings_saved'))
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
            Settings saved successfully.
        </div>
    @endif

    @if(session('action_result'))
        <div class="mb-4 rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-800">
            {{ session('action_result') }}
        </div>
    @endif

    <section class="grid gap-6 lg:grid-cols-[1.2fr,0.8fr]">
        <form action="{{ route('dashboard.settings.update') }}" method="POST" class="glass-card p-6">
            @csrf
            <h2 class="text-lg font-bold">Project Settings</h2>

            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="project_name" class="text-xs font-semibold uppercase tracking-wider text-slate-500">Project name</label>
                    <input id="project_name" name="project_name" value="{{ old('project_name', $settings['project_name']) }}" class="mt-1 w-full rounded-xl border border-slate-300 px-4 py-2 text-sm focus:border-slate-600 focus:outline-none" required>
                </div>
                <div>
                    <label for="support_email" class="text-xs font-semibold uppercase tracking-wider text-slate-500">Support email</label>
                    <input id="support_email" name="support_email" value="{{ old('support_email', $settings['support_email']) }}" class="mt-1 w-full rounded-xl border border-slate-300 px-4 py-2 text-sm focus:border-slate-600 focus:outline-none" required>
                </div>
                <div>
                    <label for="default_currency" class="text-xs font-semibold uppercase tracking-wider text-slate-500">Default currency</label>
                    <input id="default_currency" name="default_currency" value="{{ old('default_currency', $settings['default_currency']) }}" class="mt-1 w-full rounded-xl border border-slate-300 px-4 py-2 text-sm uppercase focus:border-slate-600 focus:outline-none" required>
                </div>
                <div>
                    <label for="default_timezone" class="text-xs font-semibold uppercase tracking-wider text-slate-500">Timezone</label>
                    <input id="default_timezone" name="default_timezone" value="{{ old('default_timezone', $settings['default_timezone']) }}" class="mt-1 w-full rounded-xl border border-slate-300 px-4 py-2 text-sm focus:border-slate-600 focus:outline-none" required>
                </div>
                <div>
                    <label for="high_value_review_threshold" class="text-xs font-semibold uppercase tracking-wider text-slate-500">High-value review threshold (cents)</label>
                    <input id="high_value_review_threshold" name="high_value_review_threshold" type="number" min="1000" value="{{ old('high_value_review_threshold', $settings['high_value_review_threshold']) }}" class="mt-1 w-full rounded-xl border border-slate-300 px-4 py-2 text-sm focus:border-slate-600 focus:outline-none" required>
                </div>
                <div>
                    <label for="max_retry_attempts" class="text-xs font-semibold uppercase tracking-wider text-slate-500">Max retry attempts</label>
                    <input id="max_retry_attempts" name="max_retry_attempts" type="number" min="1" max="4" value="{{ old('max_retry_attempts', $settings['max_retry_attempts']) }}" class="mt-1 w-full rounded-xl border border-slate-300 px-4 py-2 text-sm focus:border-slate-600 focus:outline-none" required>
                </div>
                <div>
                    <label for="api_rate_limit_per_minute" class="text-xs font-semibold uppercase tracking-wider text-slate-500">API rate limit (rpm)</label>
                    <input id="api_rate_limit_per_minute" name="api_rate_limit_per_minute" type="number" min="30" max="10000" value="{{ old('api_rate_limit_per_minute', $settings['api_rate_limit_per_minute']) }}" class="mt-1 w-full rounded-xl border border-slate-300 px-4 py-2 text-sm focus:border-slate-600 focus:outline-none" required>
                </div>
                <div>
                    <label for="payment_live_poll_interval_ms" class="text-xs font-semibold uppercase tracking-wider text-slate-500">Live poll interval (ms)</label>
                    <input id="payment_live_poll_interval_ms" name="payment_live_poll_interval_ms" type="number" min="1000" max="10000" value="{{ old('payment_live_poll_interval_ms', $settings['payment_live_poll_interval_ms']) }}" class="mt-1 w-full rounded-xl border border-slate-300 px-4 py-2 text-sm focus:border-slate-600 focus:outline-none" required>
                </div>
                <div>
                    <label for="checkout_default_test_card" class="text-xs font-semibold uppercase tracking-wider text-slate-500">Default checkout test card</label>
                    <input id="checkout_default_test_card" name="checkout_default_test_card" value="{{ old('checkout_default_test_card', $settings['checkout_default_test_card']) }}" class="mt-1 w-full rounded-xl border border-slate-300 px-4 py-2 text-sm focus:border-slate-600 focus:outline-none" required>
                </div>
                <div class="sm:col-span-2">
                    <label for="maintenance_message" class="text-xs font-semibold uppercase tracking-wider text-slate-500">Maintenance banner message</label>
                    <input id="maintenance_message" name="maintenance_message" value="{{ old('maintenance_message', $settings['maintenance_message']) }}" class="mt-1 w-full rounded-xl border border-slate-300 px-4 py-2 text-sm focus:border-slate-600 focus:outline-none" required>
                </div>
            </div>

            <div class="mt-5 grid gap-3 sm:grid-cols-3">
                <label class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700">
                    <input type="checkbox" name="allow_guest_checkout" value="1" {{ old('allow_guest_checkout', $settings['allow_guest_checkout']) ? 'checked' : '' }}>
                    Allow guest checkout
                </label>
                <label class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700">
                    <input type="checkbox" name="auto_finalize_orders" value="1" {{ old('auto_finalize_orders', $settings['auto_finalize_orders']) ? 'checked' : '' }}>
                    Auto finalize orders
                </label>
                <label class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700">
                    <input type="checkbox" name="enable_processing_watchdog" value="1" {{ old('enable_processing_watchdog', $settings['enable_processing_watchdog']) ? 'checked' : '' }}>
                    Enable processing watchdog
                </label>
                <label class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700">
                    <input type="checkbox" name="maintenance_banner_enabled" value="1" {{ old('maintenance_banner_enabled', $settings['maintenance_banner_enabled']) ? 'checked' : '' }}>
                    Maintenance banner enabled
                </label>
            </div>

            <button class="action-btn mt-5">Save settings</button>
        </form>

        <aside class="space-y-4">
            <article class="glass-card p-6">
                <h2 class="text-lg font-bold">Test Functions</h2>
                <p class="mt-2 text-sm text-slate-600">Generate deterministic transactions with one click.</p>

                <form action="{{ route('dashboard.settings.actions') }}" method="POST" class="mt-4 space-y-2">
                    @csrf
                    <button name="action" value="demo_success" class="action-btn w-full justify-start">Create successful payment</button>
                    <button name="action" value="demo_failure" class="action-btn-secondary w-full justify-start">Create failed payment</button>
                    <button name="action" value="demo_retry" class="action-btn-secondary w-full justify-start">Create retrying payment</button>
                    <button name="action" value="demo_pack" class="action-btn-secondary w-full justify-start">Create demo pack (3 intents)</button>
                    <button name="action" value="demo_fraud_review" class="action-btn-secondary w-full justify-start">Create high-value fraud review</button>
                    <button name="action" value="watchdog_run" class="action-btn-secondary w-full justify-start">Run processing watchdog now</button>
                </form>
            </article>

            <article class="glass-card p-6">
                <h2 class="text-lg font-bold">Where to verify</h2>
                <ul class="mt-3 space-y-2 text-sm text-slate-700">
                    <li><a href="{{ route('dashboard.payments') }}" class="underline">Payments</a> for status transitions</li>
                    <li><a href="{{ route('dashboard.events') }}" class="underline">Event Log</a> for emitted events</li>
                    <li><a href="{{ route('dashboard.system') }}" class="underline">System</a> for queue/watchdog state</li>
                </ul>
            </article>
        </aside>
    </section>
@endsection
