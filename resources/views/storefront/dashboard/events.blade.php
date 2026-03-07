@extends('storefront.layout')

@section('content')
    <section class="mb-6">
        <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500">Dashboard</p>
        <h1 class="mt-1 text-3xl font-extrabold tracking-tight">Event Log</h1>
    </section>

    @include('storefront.dashboard._nav')

    <section class="glass-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Event</th>
                        <th class="px-5 py-3">Aggregate</th>
                        <th class="px-5 py-3">Payload</th>
                        <th class="px-5 py-3">At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($eventLogs as $event)
                        <tr class="border-t border-slate-100">
                            <td class="px-5 py-3 font-semibold">{{ $event->event_name }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $event->aggregate_type }} #{{ $event->aggregate_id }}</td>
                            <td class="px-5 py-3 text-xs text-slate-500">
                                <details>
                                    <summary class="cursor-pointer font-semibold text-slate-700">Show payload</summary>
                                    <pre class="mt-2 max-h-40 overflow-auto rounded-lg bg-slate-50 p-2 text-[11px] leading-relaxed text-slate-600">{{ json_encode($event->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                </details>
                            </td>
                            <td class="px-5 py-3 text-slate-500">{{ optional($event->happened_at)->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-6 text-center text-slate-500">
                                No event records yet.
                                <a href="{{ route('products.index') }}" class="ml-2 font-semibold text-slate-700 underline">Run test checkout</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div class="mt-4">{{ $eventLogs->links() }}</div>
@endsection
