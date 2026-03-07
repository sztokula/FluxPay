@extends('storefront.layout')

@section('content')
    <section class="mb-6">
        <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500">Dashboard</p>
        <h1 class="mt-1 text-3xl font-extrabold tracking-tight">System Observability</h1>
        <p class="mt-2 text-sm text-slate-600">Queue health, retry backlog and processing watchdog visibility.</p>
    </section>

    @include('storefront.dashboard._nav')

    @if(! $health['worker_recent'] || ! $health['scheduler_recent'] || ! $health['watchdog_recent'] || $jobStats['failed'] > 0)
        <section class="mb-4 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
            <p class="font-semibold">Operational warning detected.</p>
            <ul class="mt-2 list-disc pl-5">
                @if(! $health['worker_recent'] && $health['worker_needs_recent_heartbeat'])
                    <li>Queue worker heartbeat is stale (run <code>php artisan queue:work</code>).</li>
                @endif
                @if(! $health['scheduler_recent'])
                    <li>Scheduler heartbeat is stale (run <code>php artisan schedule:work</code>).</li>
                @endif
                @if(! $health['watchdog_recent'])
                    <li>Processing watchdog did not run recently.</li>
                @endif
                @if($jobStats['failed'] > 0)
                    <li>There are failed jobs pending retry.</li>
                @endif
            </ul>
        </section>
    @endif

    <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
        <article class="glass-card p-4">
            <p class="text-xs font-semibold uppercase text-slate-500">Queued jobs</p>
            <p class="mt-2 text-2xl font-black">{{ $jobStats['queued'] }}</p>
        </article>
        <article class="glass-card p-4">
            <p class="text-xs font-semibold uppercase text-slate-500">Failed jobs</p>
            <p class="mt-2 text-2xl font-black text-red-600">{{ $jobStats['failed'] }}</p>
        </article>
        <article class="glass-card p-4">
            <p class="text-xs font-semibold uppercase text-slate-500">Processing intents</p>
            <p class="mt-2 text-2xl font-black">{{ $processingStats['processing_total'] }}</p>
        </article>
        <article class="glass-card p-4">
            <p class="text-xs font-semibold uppercase text-slate-500">Stale no-retry</p>
            <p class="mt-2 text-2xl font-black text-amber-600">{{ $processingStats['stale_without_retry'] }}</p>
        </article>
        <article class="glass-card p-4">
            <p class="text-xs font-semibold uppercase text-slate-500">Overdue retries</p>
            <p class="mt-2 text-2xl font-black text-amber-600">{{ $processingStats['overdue_retry'] }}</p>
        </article>
    </section>

    <section class="mt-6 grid gap-6 lg:grid-cols-2">
        <article class="glass-card p-6">
            <h2 class="text-lg font-bold">Queue Snapshot</h2>
            <div class="mt-4 space-y-2 text-sm text-slate-700">
                <p><span class="font-semibold">Oldest queued job id:</span> {{ $oldestQueuedJob?->id ?? '-' }}</p>
                <p><span class="font-semibold">Available at:</span> {{ $oldestQueuedJob ? date('Y-m-d H:i:s', (int) $oldestQueuedJob->available_at) : '-' }}</p>
                <p><span class="font-semibold">Worker last job:</span> {{ $workerLastJobAt?->format('Y-m-d H:i:s') ?? '-' }}</p>
                <p><span class="font-semibold">Scheduler last tick:</span> {{ $schedulerLastTickAt?->format('Y-m-d H:i:s') ?? '-' }}</p>
                <p><span class="font-semibold">Watchdog last run:</span> {{ $watchdogLastRunAt?->format('Y-m-d H:i:s') ?? '-' }}</p>
            </div>

            <pre class="mt-4 overflow-auto rounded-xl bg-slate-50 p-4 text-xs text-slate-700">php artisan queue:work
php artisan queue:failed
php artisan queue:retry all</pre>
        </article>

        <article class="glass-card p-6">
            <h2 class="text-lg font-bold">Latest Failed Job</h2>
            @if($latestFailedJob)
                <div class="mt-4 space-y-2 text-sm text-slate-700">
                    <p><span class="font-semibold">ID:</span> {{ $latestFailedJob->id }}</p>
                    <p><span class="font-semibold">Queue:</span> {{ $latestFailedJob->queue }}</p>
                    <p><span class="font-semibold">Failed at:</span> {{ $latestFailedJob->failed_at }}</p>
                </div>
            @else
                <p class="mt-4 text-sm text-slate-600">No failed jobs.</p>
            @endif

            <pre class="mt-4 overflow-auto rounded-xl bg-slate-50 p-4 text-xs text-slate-700">php artisan schedule:work
php artisan payment-intents:mark-stale-processing</pre>
        </article>
    </section>
@endsection
