<?php

namespace App\Http\Controllers\Api;

use App\Actions\CreateSubscriptionAction;
use App\Enums\SubscriptionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubscriptionRequest;
use App\Http\Resources\SubscriptionResource;
use App\Models\Customer;
use App\Models\Price;
use App\Models\Subscription;
use App\Services\WebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SubscriptionController extends Controller
{
    public function __construct(
        private CreateSubscriptionAction $createSubscriptionAction,
        private WebhookService $webhookService
    ) {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Subscription::class);

        $query = Subscription::query()
            ->whereHas('customer', fn ($customerQuery) => $customerQuery->where('user_id', $request->user()->id));

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->string('from')->toString());
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->string('to')->toString());
        }

        return SubscriptionResource::collection($query->latest()->paginate(15));
    }

    public function store(StoreSubscriptionRequest $request): JsonResponse
    {
        $this->authorize('create', Subscription::class);

        $customer = Customer::query()->findOrFail($request->integer('customer_id'));

        if ($customer->user_id !== $request->user()->id) {
            abort(403);
        }

        $price = Price::query()->with('plan')->findOrFail($request->integer('price_id'));

        if ($price->plan->is_active === false || $price->is_active === false) {
            return response()->json(['message' => 'Price is not active.'], 422);
        }

        $subscription = $this->createSubscriptionAction->execute($request->validated());

        return (new SubscriptionResource($subscription))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Subscription $subscription): SubscriptionResource
    {
        $this->authorize('view', $subscription);

        return new SubscriptionResource($subscription);
    }

    public function destroy(Subscription $subscription): \Illuminate\Http\Response
    {
        $this->authorize('delete', $subscription);

        $subscription->update([
            'status' => SubscriptionStatus::Canceled,
            'canceled_at' => now(),
            'cancel_at_period_end' => false,
        ]);

        $this->webhookService->publish(
            'subscription.canceled',
            'subscription',
            $subscription->id,
            $subscription->toArray()
        );

        return response()->noContent();
    }
}
