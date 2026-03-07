<?php

namespace App\Http\Controllers\Api;

use App\Actions\RegisterWebhookEndpointAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWebhookEndpointRequest;
use App\Http\Resources\WebhookEndpointResource;
use App\Models\EventLog;
use App\Models\WebhookEndpoint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class WebhookEndpointController extends Controller
{
    public function __construct(private RegisterWebhookEndpointAction $registerWebhookEndpointAction)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', WebhookEndpoint::class);

        return WebhookEndpointResource::collection(
            WebhookEndpoint::query()
                ->where('user_id', $request->user()->id)
                ->latest()
                ->paginate(15)
        );
    }

    public function store(StoreWebhookEndpointRequest $request): JsonResponse
    {
        $this->authorize('create', WebhookEndpoint::class);

        $payload = $request->validated();
        $payload['user_id'] = $request->user()->id;

        $endpoint = $this->registerWebhookEndpointAction->execute($payload);

        EventLog::query()->create([
            'user_id' => $request->user()->id,
            'event_name' => 'webhook_endpoint.created',
            'aggregate_type' => 'webhook_endpoint',
            'aggregate_id' => $endpoint->id,
            'payload' => $endpoint->toArray(),
            'happened_at' => now(),
        ]);

        return (new WebhookEndpointResource($endpoint))
            ->response()
            ->setStatusCode(201);
    }

    public function show(WebhookEndpoint $webhook): WebhookEndpointResource
    {
        $this->authorize('view', $webhook);

        return new WebhookEndpointResource($webhook);
    }

    public function update(StoreWebhookEndpointRequest $request, WebhookEndpoint $webhook): WebhookEndpointResource
    {
        $this->authorize('update', $webhook);

        $webhook->update($request->validated());

        EventLog::query()->create([
            'user_id' => $request->user()->id,
            'event_name' => 'webhook_endpoint.updated',
            'aggregate_type' => 'webhook_endpoint',
            'aggregate_id' => $webhook->id,
            'payload' => $webhook->toArray(),
            'happened_at' => now(),
        ]);

        return new WebhookEndpointResource($webhook->fresh());
    }

    public function destroy(WebhookEndpoint $webhook): \Illuminate\Http\Response
    {
        $this->authorize('delete', $webhook);

        $snapshot = $webhook->toArray();

        $webhook->delete();

        EventLog::query()->create([
            'user_id' => request()->user()?->id,
            'event_name' => 'webhook_endpoint.deleted',
            'aggregate_type' => 'webhook_endpoint',
            'aggregate_id' => $webhook->id,
            'payload' => $snapshot,
            'happened_at' => now(),
        ]);

        return response()->noContent();
    }
}
