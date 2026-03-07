@extends('storefront.layout')

@section('content')
    <section class="mb-6">
        <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500">Dashboard</p>
        <h1 class="mt-1 text-3xl font-extrabold tracking-tight">Webhooks</h1>
    </section>

    @include('storefront.dashboard._nav')

    <section class="glass-card overflow-hidden">
        <div class="border-b border-slate-200/70 px-5 py-4">
            <h2 class="font-bold">Endpoints</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-5 py-3">ID</th>
                        <th class="px-5 py-3">URL</th>
                        <th class="px-5 py-3">Events</th>
                        <th class="px-5 py-3">Deliveries</th>
                        <th class="px-5 py-3">Active</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($endpoints as $endpoint)
                        <tr class="border-t border-slate-100">
                            <td class="px-5 py-3 font-semibold">#{{ $endpoint->id }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $endpoint->url }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ implode(', ', $endpoint->events ?? []) }}</td>
                            <td class="px-5 py-3">{{ $endpoint->deliveries_count }}</td>
                            <td class="px-5 py-3">@include('storefront.dashboard._status', ['value' => $endpoint->is_active ? 'yes' : 'no'])</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-6 text-center text-slate-500">No webhook endpoints yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div class="mt-4">{{ $endpoints->links() }}</div>

    <section class="mt-6 glass-card overflow-hidden">
        <div class="border-b border-slate-200/70 px-5 py-4">
            <h2 class="font-bold">Recent deliveries</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Event</th>
                        <th class="px-5 py-3">Endpoint</th>
                        <th class="px-5 py-3">Attempt</th>
                        <th class="px-5 py-3">HTTP</th>
                        <th class="px-5 py-3">Error</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentDeliveries as $delivery)
                        <tr class="border-t border-slate-100">
                            <td class="px-5 py-3 font-semibold">{{ $delivery->event_name }}</td>
                            <td class="px-5 py-3 text-slate-600">
                                <p class="font-semibold">#{{ $delivery->webhook_endpoint_id }}</p>
                                <p class="truncate text-xs text-slate-500">{{ $delivery->endpoint?->url ?? 'Unknown URL' }}</p>
                            </td>
                            <td class="px-5 py-3">{{ $delivery->attempt }}</td>
                            <td class="px-5 py-3">
                                @if($delivery->last_http_code)
                                    @include('storefront.dashboard._status', ['value' => ($delivery->last_http_code >= 200 && $delivery->last_http_code < 300) ? 'succeeded' : 'failed'])
                                @else
                                    <span class="text-slate-500">-</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-slate-500">{{ $delivery->last_error ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-6 text-center text-slate-500">No webhook deliveries yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
