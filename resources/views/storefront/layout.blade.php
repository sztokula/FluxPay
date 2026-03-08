<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $projectName = app(\App\Services\AppSettingsService::class)->get('project_name', config('app.name'));
        $supportEmail = app(\App\Services\AppSettingsService::class)->get('support_email', 'support@local.test');
        $maintenanceBannerEnabled = (bool) app(\App\Services\AppSettingsService::class)->get('maintenance_banner_enabled', false);
        $maintenanceMessage = app(\App\Services\AppSettingsService::class)->get('maintenance_message', 'Maintenance mode is enabled. Some operations may be delayed.');
    @endphp
    <title>{{ $projectName }} - FluxPay</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800|sora:400,500,600,700" rel="stylesheet" />

    @php
        $hasFrontendAssets = file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot'));
    @endphp

    @if ($hasFrontendAssets)
        @vite(['resources/css/app.css'])
    @else
        <style>
            body {
                margin: 0;
                font-family: 'Sora', 'Manrope', sans-serif;
                background: linear-gradient(180deg, #f8fafc 0%, #eef2ff 100%);
                color: #0f172a;
            }

            .fallback-wrap {
                max-width: 1100px;
                margin: 0 auto;
                padding: 0 20px;
            }

            .fallback-header {
                position: sticky;
                top: 0;
                background: rgba(255, 255, 255, 0.95);
                border-bottom: 1px solid #e2e8f0;
                backdrop-filter: blur(8px);
                z-index: 20;
            }

            .fallback-main {
                max-width: 1100px;
                margin: 0 auto;
                padding: 32px 20px;
            }

            .fallback-alert {
                max-width: 1100px;
                margin: 12px auto 0;
                border: 1px solid #fcd34d;
                background: #fffbeb;
                color: #92400e;
                padding: 12px 16px;
                border-radius: 12px;
                font-size: 14px;
            }

            .glass-card {
                border: 1px solid #e2e8f0;
                background: #ffffff;
                border-radius: 16px;
                box-shadow: 0 8px 30px rgba(15, 23, 42, 0.06);
            }

            .action-btn,
            .action-btn-secondary {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 10px 16px;
                border-radius: 12px;
                text-decoration: none;
                font-weight: 600;
                font-size: 14px;
            }

            .action-btn {
                background: #0f172a;
                color: #fff;
            }

            .action-btn-secondary {
                border: 1px solid #cbd5e1;
                background: #fff;
                color: #334155;
            }

            .status-pill {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                border-radius: 9999px;
                border: 1px solid #e2e8f0;
                padding: 3px 10px;
                font-size: 12px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.04em;
            }

            .status-dot {
                width: 8px;
                height: 8px;
                border-radius: 50%;
            }

            .border-emerald-200 { border-color: #a7f3d0; }
            .bg-emerald-50 { background: #ecfdf5; }
            .text-emerald-700 { color: #047857; }
            .bg-emerald-500 { background: #10b981; }

            .border-amber-200 { border-color: #fde68a; }
            .bg-amber-50 { background: #fffbeb; }
            .text-amber-700 { color: #b45309; }
            .bg-amber-500 { background: #f59e0b; }

            .border-red-200 { border-color: #fecaca; }
            .bg-red-50 { background: #fef2f2; }
            .text-red-700 { color: #b91c1c; }
            .bg-red-500 { background: #ef4444; }

            .border-slate-200 { border-color: #e2e8f0; }
            .bg-slate-50 { background: #f8fafc; }
            .text-slate-700 { color: #334155; }
            .bg-slate-500 { background: #64748b; }

            .live-pulse {
                animation: livePulse 1.2s ease-in-out infinite;
            }

            @keyframes livePulse {
                0% { transform: scale(1); opacity: 0.6; }
                50% { transform: scale(1.35); opacity: 1; }
                100% { transform: scale(1); opacity: 0.6; }
            }

            table {
                width: 100%;
                border-collapse: collapse;
            }

            th,
            td {
                padding: 10px 12px;
                border-top: 1px solid #f1f5f9;
                text-align: left;
                font-size: 14px;
            }

            th {
                color: #64748b;
                font-size: 12px;
                text-transform: uppercase;
                letter-spacing: 0.04em;
                background: #f8fafc;
            }
        </style>
    @endif
</head>
<body class="min-h-screen text-slate-900">
    <div class="fixed inset-0 -z-10 overflow-hidden">
        <div class="absolute -left-32 top-20 h-72 w-72 rounded-full bg-emerald-300/30 blur-3xl"></div>
        <div class="absolute right-0 top-0 h-96 w-96 rounded-full bg-blue-300/30 blur-3xl"></div>
        <div class="absolute bottom-0 left-1/3 h-72 w-72 rounded-full bg-cyan-200/30 blur-3xl"></div>
    </div>

    <header class="sticky top-0 z-20 border-b border-white/70 bg-white/75 backdrop-blur {{ $hasFrontendAssets ? '' : 'fallback-header' }}">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-5 py-4 sm:px-6">
            <a href="{{ route('home') }}" class="text-lg font-extrabold tracking-tight sm:text-xl">
                <span class="rounded-lg bg-slate-900 px-2 py-1 text-xs font-bold uppercase tracking-wider text-white">Demo</span>
                <span class="ml-2">FluxPay</span>
            </a>

            <nav class="hidden flex-wrap items-center justify-end gap-2 text-sm font-semibold md:flex md:gap-3">
                <a href="{{ route('home') }}" class="rounded-lg px-3 py-1.5 {{ request()->routeIs('home') ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">Home</a>
                <a href="{{ route('products.index') }}" class="rounded-lg px-3 py-1.5 {{ request()->routeIs('products.*') || request()->routeIs('checkout.*') || request()->routeIs('payment.*') ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">Storefront</a>
                <a href="https://github.com/sztokula/FluxPay" target="_blank" rel="noopener noreferrer" class="rounded-lg px-3 py-1.5 text-slate-600 hover:bg-slate-100 hover:text-slate-900">GitHub</a>

                <details class="group relative">
                    <summary class="list-none cursor-pointer rounded-lg px-3 py-1.5 text-slate-600 hover:bg-slate-100 hover:text-slate-900 {{ request()->routeIs('docs.*') ? 'bg-slate-900 text-white hover:bg-slate-900 hover:text-white' : '' }}">
                        Docs
                    </summary>
                    <div class="absolute right-0 top-10 z-30 w-52 rounded-xl border border-slate-200 bg-white p-2 shadow-xl">
                        <a href="{{ route('docs.documentation') }}" class="block rounded-lg px-3 py-2 text-sm {{ request()->routeIs('docs.documentation') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">Documentation</a>
                        <a href="{{ route('docs.changelog') }}" class="mt-1 block rounded-lg px-3 py-2 text-sm {{ request()->routeIs('docs.changelog') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">Changelog</a>
                        <a href="{{ route('docs.lessons') }}" class="mt-1 block rounded-lg px-3 py-2 text-sm {{ request()->routeIs('docs.lessons') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">What I Learned</a>
                    </div>
                </details>

                @auth
                    <details class="group relative">
                        <summary class="list-none cursor-pointer rounded-lg px-3 py-1.5 text-slate-600 hover:bg-slate-100 hover:text-slate-900 {{ request()->routeIs('dashboard.*') || request()->routeIs('api.reference') ? 'bg-slate-900 text-white hover:bg-slate-900 hover:text-white' : '' }}">
                            Workspace
                        </summary>
                        <div class="absolute right-0 top-10 z-30 w-44 rounded-xl border border-slate-200 bg-white p-2 shadow-xl">
                            <a href="{{ route('dashboard.overview') }}" class="block rounded-lg px-3 py-2 text-sm {{ request()->routeIs('dashboard.*') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">Dashboard</a>
                            <a href="{{ route('api.reference') }}" class="mt-1 block rounded-lg px-3 py-2 text-sm {{ request()->routeIs('api.reference') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">API Reference</a>
                        </div>
                    </details>
                    <form method="POST" action="{{ route('auth.logout') }}">
                        @csrf
                        <button class="rounded-lg px-3 py-1.5 text-slate-600 hover:bg-slate-100 hover:text-slate-900">Logout</button>
                    </form>
                @else
                    <a href="{{ route('auth.login.form') }}" class="rounded-lg px-3 py-1.5 {{ request()->routeIs('auth.login.form') ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">Login</a>
                    <a href="{{ route('auth.register.form') }}" class="rounded-lg px-3 py-1.5 {{ request()->routeIs('auth.register.form') ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">Register</a>
                @endauth
            </nav>

            <details class="relative md:hidden">
                <summary class="list-none rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-semibold text-slate-700">
                    Menu
                </summary>
                <div class="absolute right-0 top-11 z-30 w-64 rounded-xl border border-slate-200 bg-white p-2 shadow-xl">
                    <a href="{{ route('home') }}" class="block rounded-lg px-3 py-2 text-sm {{ request()->routeIs('home') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">Home</a>
                    <a href="{{ route('products.index') }}" class="mt-1 block rounded-lg px-3 py-2 text-sm {{ request()->routeIs('products.*') || request()->routeIs('checkout.*') || request()->routeIs('payment.*') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">Storefront</a>
                    <a href="https://github.com/sztokula/FluxPay" target="_blank" rel="noopener noreferrer" class="mt-1 block rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-slate-100">GitHub</a>

                    <p class="mt-3 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-500">Docs</p>
                    <a href="{{ route('docs.documentation') }}" class="mt-1 block rounded-lg px-3 py-2 text-sm {{ request()->routeIs('docs.documentation') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">Documentation</a>
                    <a href="{{ route('docs.changelog') }}" class="mt-1 block rounded-lg px-3 py-2 text-sm {{ request()->routeIs('docs.changelog') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">Changelog</a>
                    <a href="{{ route('docs.lessons') }}" class="mt-1 block rounded-lg px-3 py-2 text-sm {{ request()->routeIs('docs.lessons') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">What I Learned</a>

                    @auth
                        <p class="mt-3 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-500">Workspace</p>
                        <a href="{{ route('dashboard.overview') }}" class="mt-1 block rounded-lg px-3 py-2 text-sm {{ request()->routeIs('dashboard.*') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">Dashboard</a>
                        <a href="{{ route('api.reference') }}" class="mt-1 block rounded-lg px-3 py-2 text-sm {{ request()->routeIs('api.reference') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">API Reference</a>
                        <form method="POST" action="{{ route('auth.logout') }}" class="mt-1">
                            @csrf
                            <button class="block w-full rounded-lg px-3 py-2 text-left text-sm text-slate-700 hover:bg-slate-100">Logout</button>
                        </form>
                    @else
                        <a href="{{ route('auth.login.form') }}" class="mt-3 block rounded-lg px-3 py-2 text-sm {{ request()->routeIs('auth.login.form') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">Login</a>
                        <a href="{{ route('auth.register.form') }}" class="mt-1 block rounded-lg px-3 py-2 text-sm {{ request()->routeIs('auth.register.form') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">Register</a>
                    @endauth
                </div>
            </details>
        </div>
    </header>

    @unless($hasFrontendAssets)
        <div class="fallback-alert">
            Frontend assets are not built, so Tailwind styles are limited. Run <code>npm run dev</code> (or <code>npm run build</code>) and refresh.
        </div>
    @endunless

    @if($maintenanceBannerEnabled)
        <div class="mx-auto mt-3 max-w-6xl rounded-xl border border-amber-200 bg-amber-50 px-5 py-3 text-sm font-semibold text-amber-800 sm:px-6">
            {{ $maintenanceMessage }}
        </div>
    @endif

    <main class="mx-auto max-w-6xl px-5 py-8 sm:px-6 sm:py-10 {{ $hasFrontendAssets ? '' : 'fallback-main' }}">
        @yield('content')
    </main>

    <footer class="mx-auto max-w-6xl px-5 pb-8 text-xs text-slate-500 sm:px-6">
        <p>Tip: for real-time payment updates run queue worker: <code class="rounded bg-slate-100 px-1 py-0.5">php artisan queue:work</code></p>
        <p class="mt-1">Support: {{ $supportEmail }}</p>
        <p class="mt-1">
            <a href="{{ route('docs.documentation') }}" class="underline">Documentation</a> |
            <a href="{{ route('docs.changelog') }}" class="underline">Changelog</a> |
            <a href="{{ route('docs.lessons') }}" class="underline">What I Learned</a>
        </p>
    </footer>
</body>
</html>
