@extends('storefront.layout')

@section('content')
    <section class="mx-auto max-w-6xl space-y-6">
        <div class="glass-card p-8 sm:p-10">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500">Developer API</p>
                    <h1 class="mt-2 text-3xl font-extrabold tracking-tight sm:text-4xl">API Reference</h1>
                    <p class="mt-3 max-w-3xl text-sm text-slate-600">
                        REST API for customers, payment intents, subscriptions, invoices, payouts, webhooks and ledger events.
                        All <code>/api/*</code> endpoints use <code>auth:sanctum</code> and return JSON.
                    </p>
                </div>
                <span class="status-pill border-emerald-200 bg-emerald-50 text-emerald-700">
                    <span class="status-dot bg-emerald-500"></span>
                    v1 local
                </span>
            </div>

            <div class="mt-6 grid gap-3 sm:grid-cols-3">
                <article class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase text-slate-500">Base URL</p>
                    <p class="mt-2 break-all text-sm font-bold text-slate-800">{{ url('/api') }}</p>
                </article>
                <article class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase text-slate-500">Content type</p>
                    <p class="mt-2 text-sm font-bold text-slate-800">application/json</p>
                </article>
                <article class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase text-slate-500">Auth</p>
                    <p class="mt-2 text-sm font-bold text-slate-800">Bearer token (Sanctum)</p>
                </article>
            </div>

            <div class="mt-6 flex flex-wrap gap-2">
                <a href="{{ route('products.index') }}" class="action-btn-secondary">Storefront</a>
                <a href="{{ route('dashboard.overview') }}" class="action-btn-secondary">Dashboard</a>
                <form method="POST" action="{{ route('auth.logout') }}">
                    @csrf
                    <button class="action-btn-secondary">Logout</button>
                </form>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-[1.1fr,0.9fr]">
            <section class="glass-card p-8">
                <h2 class="text-xl font-bold tracking-tight">Access Tokens</h2>
                <p class="mt-2 text-sm text-slate-600">API account: <span class="font-semibold">{{ $currentUser->email }}</span></p>

                @if($plainTextToken)
                    <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                        <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">New token (copy now)</p>
                        <pre id="new-api-token" class="mt-2 overflow-auto rounded-lg bg-white p-3 text-xs text-slate-800">{{ $plainTextToken }}</pre>
                        <button id="copy-new-token" type="button" class="action-btn-secondary mt-2 !px-3 !py-1.5 !text-xs">Copy token</button>
                    </div>
                @endif

                <form action="{{ route('api.tokens.create') }}" method="POST" class="mt-4 flex flex-wrap gap-2">
                    @csrf
                    <input
                        name="token_name"
                        value="local-dev-token"
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm focus:border-slate-600 focus:outline-none sm:w-auto"
                        placeholder="Token name"
                        required
                    >
                    <button class="action-btn">Create token</button>
                </form>

                @error('token_name')
                    <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                @enderror

                <div class="mt-5 space-y-2">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Existing tokens</p>
                    @forelse($tokens as $token)
                        <form action="{{ route('api.tokens.revoke') }}" method="POST" class="flex items-center justify-between gap-2 rounded-xl border border-slate-200 bg-slate-50 p-3">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="token_id" value="{{ $token->id }}">
                            <div class="text-sm">
                                <p class="font-semibold text-slate-800">{{ $token->name }}</p>
                                <p class="text-xs text-slate-500">Created: {{ $token->created_at->format('Y-m-d H:i') }} | Last used: {{ optional($token->last_used_at)->format('Y-m-d H:i') ?? 'never' }}</p>
                            </div>
                            <button class="action-btn-secondary !px-3 !py-1.5 !text-xs">Revoke</button>
                        </form>
                    @empty
                        <p class="rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm text-slate-600">No tokens created yet.</p>
                    @endforelse
                </div>
            </section>

            <section class="glass-card p-8">
                <h2 class="text-xl font-bold tracking-tight">Authentication</h2>
                <p class="mt-2 text-sm text-slate-600">If token is missing/invalid API returns <code>401 Unauthorized</code>.</p>
                <pre class="mt-4 overflow-auto rounded-xl bg-slate-50 p-4 text-xs text-slate-700">Authorization: Bearer YOUR_TOKEN
Accept: application/json
Content-Type: application/json</pre>

                <h3 class="mt-6 text-sm font-bold uppercase tracking-wider text-slate-500">Idempotency</h3>
                <p class="mt-2 text-sm text-slate-600">Use <code>Idempotency-Key</code> for safe retries on mutating requests.</p>
                <pre class="mt-3 overflow-auto rounded-xl bg-slate-50 p-4 text-xs text-slate-700">Idempotency-Key: 8c2b4f-request-001</pre>

                <h3 class="mt-6 text-sm font-bold uppercase tracking-wider text-slate-500">Quick Request</h3>
                <pre class="mt-3 overflow-auto rounded-xl bg-slate-900 p-4 text-xs text-slate-100">curl -X GET "{{ url('/api/payment-intents') }}" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"</pre>
                <p class="mt-3 text-xs text-slate-500">OpenAPI spec file: <code>openapi/openapi.yaml</code></p>
            </section>

            <section class="glass-card p-8">
                <h2 class="text-xl font-bold tracking-tight">Status Codes</h2>
                <div class="mt-4 space-y-2 text-sm">
                    <p class="flex items-center gap-2"><span class="status-pill border-emerald-200 bg-emerald-50 text-emerald-700"><span class="status-dot bg-emerald-500"></span>200/201</span> Success</p>
                    <p class="flex items-center gap-2"><span class="status-pill border-amber-200 bg-amber-50 text-amber-700"><span class="status-dot bg-amber-500"></span>401</span> Unauthenticated</p>
                    <p class="flex items-center gap-2"><span class="status-pill border-red-200 bg-red-50 text-red-700"><span class="status-dot bg-red-500"></span>403</span> Forbidden</p>
                    <p class="flex items-center gap-2"><span class="status-pill border-red-200 bg-red-50 text-red-700"><span class="status-dot bg-red-500"></span>422</span> Validation failed</p>
                </div>

                <h3 class="mt-6 text-sm font-bold uppercase tracking-wider text-slate-500">Error Shape</h3>
                <pre class="mt-3 overflow-auto rounded-xl bg-slate-50 p-4 text-xs text-slate-700">{
  "message": "Unauthenticated.",
  "correlation_id": "0f388ce4-bf2c-43cc-a292-57a1816195f6"
}</pre>
            </section>
        </div>

        <section class="glass-card overflow-hidden">
            <div class="border-b border-slate-200/70 px-6 py-4">
                <h2 class="text-xl font-bold tracking-tight">Endpoints</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-5 py-3">Method</th>
                            <th class="px-5 py-3">Path</th>
                            <th class="px-5 py-3">Purpose</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-t border-slate-100">
                            <td class="px-5 py-3"><span class="status-pill border-emerald-200 bg-emerald-50 text-emerald-700"><span class="status-dot bg-emerald-500"></span>GET</span></td>
                            <td class="px-5 py-3 font-mono text-xs text-slate-700">/api/customers</td>
                            <td class="px-5 py-3 text-slate-600">List customers for current user</td>
                        </tr>
                        <tr class="border-t border-slate-100">
                            <td class="px-5 py-3"><span class="status-pill border-blue-200 bg-blue-50 text-blue-700"><span class="status-dot bg-blue-500"></span>POST</span></td>
                            <td class="px-5 py-3 font-mono text-xs text-slate-700">/api/payment-intents</td>
                            <td class="px-5 py-3 text-slate-600">Create payment intent</td>
                        </tr>
                        <tr class="border-t border-slate-100">
                            <td class="px-5 py-3"><span class="status-pill border-emerald-200 bg-emerald-50 text-emerald-700"><span class="status-dot bg-emerald-500"></span>GET</span></td>
                            <td class="px-5 py-3 font-mono text-xs text-slate-700">/api/payment-intents</td>
                            <td class="px-5 py-3 text-slate-600">List payment intents</td>
                        </tr>
                        <tr class="border-t border-slate-100">
                            <td class="px-5 py-3"><span class="status-pill border-blue-200 bg-blue-50 text-blue-700"><span class="status-dot bg-blue-500"></span>POST</span></td>
                            <td class="px-5 py-3 font-mono text-xs text-slate-700">/api/payment-intents/{paymentIntent}/confirm</td>
                            <td class="px-5 py-3 text-slate-600">Confirm and process card payment</td>
                        </tr>
                        <tr class="border-t border-slate-100">
                            <td class="px-5 py-3"><span class="status-pill border-blue-200 bg-blue-50 text-blue-700"><span class="status-dot bg-blue-500"></span>POST</span></td>
                            <td class="px-5 py-3 font-mono text-xs text-slate-700">/api/payment-intents/{paymentIntent}/cancel</td>
                            <td class="px-5 py-3 text-slate-600">Cancel intent</td>
                        </tr>
                        <tr class="border-t border-slate-100">
                            <td class="px-5 py-3"><span class="status-pill border-emerald-200 bg-emerald-50 text-emerald-700"><span class="status-dot bg-emerald-500"></span>GET</span></td>
                            <td class="px-5 py-3 font-mono text-xs text-slate-700">/api/subscriptions</td>
                            <td class="px-5 py-3 text-slate-600">List subscriptions</td>
                        </tr>
                        <tr class="border-t border-slate-100">
                            <td class="px-5 py-3"><span class="status-pill border-emerald-200 bg-emerald-50 text-emerald-700"><span class="status-dot bg-emerald-500"></span>GET</span></td>
                            <td class="px-5 py-3 font-mono text-xs text-slate-700">/api/invoices</td>
                            <td class="px-5 py-3 text-slate-600">List invoices</td>
                        </tr>
                        <tr class="border-t border-slate-100">
                            <td class="px-5 py-3"><span class="status-pill border-emerald-200 bg-emerald-50 text-emerald-700"><span class="status-dot bg-emerald-500"></span>GET</span></td>
                            <td class="px-5 py-3 font-mono text-xs text-slate-700">/api/webhooks</td>
                            <td class="px-5 py-3 text-slate-600">List webhook endpoints</td>
                        </tr>
                        <tr class="border-t border-slate-100">
                            <td class="px-5 py-3"><span class="status-pill border-emerald-200 bg-emerald-50 text-emerald-700"><span class="status-dot bg-emerald-500"></span>GET</span></td>
                            <td class="px-5 py-3 font-mono text-xs text-slate-700">/api/system/health</td>
                            <td class="px-5 py-3 text-slate-600">Queue/process heartbeat snapshot</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="grid gap-6 lg:grid-cols-2">
            <div class="glass-card p-8">
                <h2 class="text-xl font-bold tracking-tight">Create Payment Intent</h2>
                <pre class="mt-4 overflow-auto rounded-xl bg-slate-900 p-4 text-xs text-slate-100">curl -X POST "{{ url('/api/payment-intents') }}" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "customer_id": 1,
    "amount": 2900,
    "currency": "USD",
    "metadata": {"product_id": 1}
  }'</pre>
            </div>
            <div class="glass-card p-8">
                <h2 class="text-xl font-bold tracking-tight">Confirm Payment Intent</h2>
                <pre class="mt-4 overflow-auto rounded-xl bg-slate-900 p-4 text-xs text-slate-100">curl -X POST "{{ url('/api/payment-intents/{id}/confirm') }}" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "card_number": "4242424242424242"
  }'</pre>
            </div>
        </section>

        <section class="glass-card p-8">
            <h2 class="text-xl font-bold tracking-tight">Try API Console</h2>
            <p class="mt-2 text-sm text-slate-600">Run authenticated requests directly from this page.</p>

            <div class="mt-4">
                <label for="api-console-template" class="text-xs font-bold uppercase tracking-wider text-slate-500">Request template</label>
                <select id="api-console-template" class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm font-semibold focus:border-slate-600 focus:outline-none">
                    <option value="custom">Custom</option>
                    <option value="list_intents">List payment intents</option>
                    <option value="create_intent">Create payment intent</option>
                    <option value="confirm_intent">Confirm payment intent</option>
                    <option value="system_health">System health</option>
                </select>
            </div>

            <div class="mt-4 grid gap-3 lg:grid-cols-[0.25fr,0.75fr]">
                <select id="api-console-method" class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm font-semibold focus:border-slate-600 focus:outline-none">
                    <option value="GET">GET</option>
                    <option value="POST">POST</option>
                    <option value="PUT">PUT</option>
                    <option value="PATCH">PATCH</option>
                    <option value="DELETE">DELETE</option>
                </select>
                <input id="api-console-path" value="/api/payment-intents" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm focus:border-slate-600 focus:outline-none" />
            </div>

            <input id="api-console-token" class="mt-3 w-full rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm focus:border-slate-600 focus:outline-none" placeholder="Bearer token">
            <div class="mt-3 grid gap-3 lg:grid-cols-2">
                <input id="api-console-idempotency" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm focus:border-slate-600 focus:outline-none" placeholder="Idempotency-Key (optional)">
                <input id="api-console-correlation" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm focus:border-slate-600 focus:outline-none" placeholder="X-Correlation-ID (optional)">
            </div>

            <textarea id="api-console-body" class="mt-3 h-36 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 font-mono text-xs focus:border-slate-600 focus:outline-none">{
  "customer_id": 1,
  "amount": 2900,
  "currency": "USD",
  "metadata": {"product_id": 1}
}</textarea>

            <button id="api-console-run" type="button" class="action-btn mt-3">Run request</button>

            <pre id="api-console-output" class="mt-4 min-h-36 overflow-auto rounded-xl bg-slate-900 p-4 text-xs text-slate-100">Response will appear here.</pre>
        </section>
    </section>

    <script>
        (() => {
            const methodInput = document.getElementById('api-console-method');
            const templateInput = document.getElementById('api-console-template');
            const pathInput = document.getElementById('api-console-path');
            const tokenInput = document.getElementById('api-console-token');
            const bodyInput = document.getElementById('api-console-body');
            const idempotencyInput = document.getElementById('api-console-idempotency');
            const correlationInput = document.getElementById('api-console-correlation');
            const runButton = document.getElementById('api-console-run');
            const output = document.getElementById('api-console-output');
            const copyTokenButton = document.getElementById('copy-new-token');
            const newTokenBlock = document.getElementById('new-api-token');

            if (!methodInput || !templateInput || !pathInput || !tokenInput || !bodyInput || !idempotencyInput || !correlationInput || !runButton || !output) {
                return;
            }

            const templates = {
                list_intents: {
                    method: 'GET',
                    path: '/api/payment-intents',
                    body: '',
                },
                create_intent: {
                    method: 'POST',
                    path: '/api/payment-intents',
                    body: JSON.stringify({
                        customer_id: 1,
                        amount: 2900,
                        currency: 'USD',
                        metadata: { product_id: 1 },
                    }, null, 2),
                },
                confirm_intent: {
                    method: 'POST',
                    path: '/api/payment-intents/{id}/confirm',
                    body: JSON.stringify({
                        card_number: '4242424242424242',
                    }, null, 2),
                },
                system_health: {
                    method: 'GET',
                    path: '/api/system/health',
                    body: '',
                },
            };

            templateInput.addEventListener('change', () => {
                const key = templateInput.value;
                if (key === 'custom' || !templates[key]) {
                    return;
                }

                methodInput.value = templates[key].method;
                pathInput.value = templates[key].path;
                bodyInput.value = templates[key].body;
            });

            if (copyTokenButton && newTokenBlock) {
                copyTokenButton.addEventListener('click', async () => {
                    const value = newTokenBlock.textContent ? newTokenBlock.textContent.trim() : '';
                    if (!value) {
                        return;
                    }

                    try {
                        await navigator.clipboard.writeText(value);
                        copyTokenButton.textContent = 'Copied';
                    } catch (error) {
                        copyTokenButton.textContent = 'Copy failed';
                    }
                });
            }

            runButton.addEventListener('click', async () => {
                const method = methodInput.value;
                const path = pathInput.value.trim();
                const token = tokenInput.value.trim();
                const idempotencyKey = idempotencyInput.value.trim();
                const correlationId = correlationInput.value.trim();

                if (!path.startsWith('/api/')) {
                    output.textContent = 'Path must start with /api/.';
                    return;
                }

                if (token.length === 0) {
                    output.textContent = 'Provide Bearer token.';
                    return;
                }

                const headers = {
                    'Accept': 'application/json',
                    'Authorization': 'Bearer ' + token,
                    'Content-Type': 'application/json',
                };

                if (idempotencyKey.length > 0) {
                    headers['Idempotency-Key'] = idempotencyKey;
                } else if (method !== 'GET' && method !== 'DELETE') {
                    headers['Idempotency-Key'] = crypto.randomUUID();
                }

                if (correlationId.length > 0) {
                    headers['X-Correlation-ID'] = correlationId;
                }

                const options = { method, headers };
                if (method !== 'GET' && method !== 'DELETE') {
                    try {
                        options.body = bodyInput.value.trim() ? JSON.stringify(JSON.parse(bodyInput.value)) : '{}';
                    } catch (error) {
                        output.textContent = 'Invalid JSON body.';
                        return;
                    }
                }

                output.textContent = 'Loading...';

                try {
                    const response = await fetch(path, options);
                    const text = await response.text();
                    let parsedBody = text;
                    try {
                        parsedBody = JSON.stringify(JSON.parse(text), null, 2);
                    } catch (error) {
                        parsedBody = text;
                    }

                    const correlationId = response.headers.get('X-Correlation-ID');
                    output.textContent = [
                        'HTTP ' + response.status + ' ' + response.statusText,
                        correlationId ? 'X-Correlation-ID: ' + correlationId : 'X-Correlation-ID: (none)',
                        '',
                        parsedBody,
                    ].join('\n');
                } catch (error) {
                    output.textContent = 'Request error: ' + (error && error.message ? error.message : 'unknown error');
                }
            });
        })();
    </script>
@endsection
