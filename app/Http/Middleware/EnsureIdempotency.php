<?php

namespace App\Http\Middleware;

use App\Models\EventLog;
use App\Services\IdempotencyService;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIdempotency
{
    public function __construct(private IdempotencyService $idempotencyService)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (! in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return $next($request);
        }

        $idempotencyKey = $request->header('Idempotency-Key');

        if (! $idempotencyKey) {
            return $next($request);
        }

        $cached = $this->idempotencyService->find(
            key: $idempotencyKey,
            route: $request->path(),
            method: $request->method(),
            requestPayload: $request->all()
        );

        if ($cached !== null) {
            if ($cached['code'] === 409) {
                EventLog::query()->create([
                    'user_id' => $request->user()?->id,
                    'event_name' => 'idempotency.conflict',
                    'aggregate_type' => 'idempotency',
                    'aggregate_id' => 0,
                    'payload' => [
                        'route' => $request->path(),
                        'method' => $request->method(),
                        'key' => $idempotencyKey,
                    ],
                    'happened_at' => now(),
                ]);
            } else {
                EventLog::query()->create([
                    'user_id' => $request->user()?->id,
                    'event_name' => 'idempotency.cache_hit',
                    'aggregate_type' => 'idempotency',
                    'aggregate_id' => 0,
                    'payload' => [
                        'route' => $request->path(),
                        'method' => $request->method(),
                        'key' => $idempotencyKey,
                    ],
                    'happened_at' => now(),
                ]);
            }

            if ($cached['code'] === 204) {
                return response()->noContent();
            }

            return new JsonResponse($cached['body'], $cached['code']);
        }

        $response = $next($request);

        if ($response instanceof JsonResponse) {
            $this->idempotencyService->store(
                key: $idempotencyKey,
                route: $request->path(),
                method: $request->method(),
                requestPayload: $request->all(),
                responseCode: $response->getStatusCode(),
                responseBody: $response->getData(true)
            );
        } elseif ($response->getStatusCode() === 204) {
            $this->idempotencyService->store(
                key: $idempotencyKey,
                route: $request->path(),
                method: $request->method(),
                requestPayload: $request->all(),
                responseCode: 204,
                responseBody: []
            );
        }

        return $response;
    }
}
