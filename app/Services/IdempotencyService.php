<?php

namespace App\Services;

use App\Models\IdempotencyKey;

class IdempotencyService
{
    /**
     * @param  array<string, mixed>  $requestPayload
     * @return array{code:int, body:array<string, mixed>}|null
     */
    public function find(string $key, string $route, string $method, array $requestPayload): ?array
    {
        $requestHash = hash('sha256', json_encode($requestPayload, JSON_THROW_ON_ERROR));

        $record = IdempotencyKey::query()
            ->where('idempotency_key', $key)
            ->where('route', $route)
            ->where('method', $method)
            ->first();

        if (! $record) {
            return null;
        }

        if ($record->request_hash !== $requestHash) {
            return ['code' => 409, 'body' => ['message' => 'Idempotency key re-used with different payload.']];
        }

        return ['code' => $record->response_code, 'body' => $record->response_body];
    }

    /**
     * @param  array<string, mixed>  $requestPayload
     * @param  array<string, mixed>  $responseBody
     */
    public function store(
        string $key,
        string $route,
        string $method,
        array $requestPayload,
        int $responseCode,
        array $responseBody
    ): void {
        IdempotencyKey::query()->updateOrCreate(
            [
                'idempotency_key' => $key,
                'route' => $route,
                'method' => $method,
            ],
            [
                'request_hash' => hash('sha256', json_encode($requestPayload, JSON_THROW_ON_ERROR)),
                'response_code' => $responseCode,
                'response_body' => $responseBody,
            ]
        );
    }
}
