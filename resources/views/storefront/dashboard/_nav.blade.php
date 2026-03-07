@php
    $dashboardSections = [
        'Core' => [
            ['label' => 'Overview', 'route' => 'dashboard.overview'],
            ['label' => 'Payments', 'route' => 'dashboard.payments'],
            ['label' => 'Events', 'route' => 'dashboard.events'],
            ['label' => 'Orders', 'route' => 'dashboard.orders'],
        ],
        'Billing' => [
            ['label' => 'Subscriptions', 'route' => 'dashboard.subscriptions'],
            ['label' => 'Invoices', 'route' => 'dashboard.invoices'],
            ['label' => 'Plans', 'route' => 'dashboard.plans'],
            ['label' => 'Payouts', 'route' => 'dashboard.payouts'],
        ],
        'Risk & Integrations' => [
            ['label' => 'Fraud', 'route' => 'dashboard.fraud'],
            ['label' => 'Webhooks', 'route' => 'dashboard.webhooks'],
            ['label' => 'Ledger', 'route' => 'dashboard.ledger'],
            ['label' => 'Customers', 'route' => 'dashboard.customers'],
        ],
        'Operations' => [
            ['label' => 'System', 'route' => 'dashboard.system'],
            ['label' => 'Settings', 'route' => 'dashboard.settings'],
        ],
    ];
@endphp

<div class="mb-6 space-y-3">
    <div class="glass-card p-3 sm:hidden">
        <label for="dashboard-quick-jump" class="text-xs font-semibold uppercase tracking-wider text-slate-500">Quick jump</label>
        <select
            id="dashboard-quick-jump"
            class="mt-2 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 focus:border-slate-600 focus:outline-none"
            onchange="if (this.value) window.location.href = this.value;"
        >
            <option value="">Select dashboard page</option>
            @foreach($dashboardSections as $sectionName => $links)
                <optgroup label="{{ $sectionName }}">
                    @foreach($links as $link)
                        <option value="{{ route($link['route']) }}" @selected(request()->routeIs($link['route'].'*'))>{{ $link['label'] }}</option>
                    @endforeach
                </optgroup>
            @endforeach
        </select>
    </div>

    <section class="glass-card hidden p-4 sm:block">
        <div class="space-y-3">
            @foreach($dashboardSections as $sectionName => $links)
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500">{{ $sectionName }}</p>
                    <nav class="mt-2 flex flex-wrap gap-2">
                        @foreach($links as $link)
                            <a
                                href="{{ route($link['route']) }}"
                                aria-current="{{ request()->routeIs($link['route'].'*') ? 'page' : 'false' }}"
                                class="rounded-lg px-3 py-1.5 text-xs font-semibold {{ request()->routeIs($link['route'].'*') ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}"
                            >
                                {{ $link['label'] }}
                            </a>
                        @endforeach
                    </nav>
                </div>
            @endforeach
        </div>
    </section>

    <div class="flex flex-wrap gap-2">
        <a href="{{ route('products.index') }}" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100">Storefront</a>
        <a href="{{ route('api.reference') }}" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100">API Reference</a>
    </div>
</div>
