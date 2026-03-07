<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentIntent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SystemHealthController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $jobsQueued = Schema::hasTable('jobs') ? DB::table('jobs')->count() : 0;
        $jobsFailed = Schema::hasTable('failed_jobs') ? DB::table('failed_jobs')->count() : 0;

        $processingTotal = PaymentIntent::query()
            ->where('status', 'processing')
            ->count();

        $workerLastJobAt = $this->cachedCarbon('system.worker_last_job_at');
        $schedulerLastTickAt = $this->cachedCarbon('system.scheduler_last_tick_at');
        $watchdogLastRunAt = $this->cachedCarbon('system.watchdog_last_run_at');

        return response()->json([
            'queue' => [
                'jobs_queued' => $jobsQueued,
                'jobs_failed' => $jobsFailed,
            ],
            'payments' => [
                'processing_total' => $processingTotal,
            ],
            'heartbeats' => [
                'worker_last_job_at' => $workerLastJobAt?->toIso8601String(),
                'scheduler_last_tick_at' => $schedulerLastTickAt?->toIso8601String(),
                'watchdog_last_run_at' => $watchdogLastRunAt?->toIso8601String(),
                'worker_recent' => $workerLastJobAt?->greaterThanOrEqualTo(now()->subMinutes(5)) ?? false,
                'scheduler_recent' => $schedulerLastTickAt?->greaterThanOrEqualTo(now()->subMinutes(2)) ?? false,
                'watchdog_recent' => $watchdogLastRunAt?->greaterThanOrEqualTo(now()->subMinutes(2)) ?? false,
            ],
            'correlation_id' => $request->attributes->get('correlation_id'),
        ]);
    }

    private function cachedCarbon(string $key): ?Carbon
    {
        $raw = Cache::get($key);

        return is_string($raw) ? Carbon::parse($raw) : null;
    }
}
