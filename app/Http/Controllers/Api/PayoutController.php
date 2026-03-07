<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePayoutRequest;
use App\Http\Resources\PayoutResource;
use App\Models\Customer;
use App\Models\Payout;
use App\Services\PayoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use RuntimeException;

class PayoutController extends Controller
{
    public function __construct(private PayoutService $payoutService)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Payout::class);

        $query = Payout::query()
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

        return PayoutResource::collection($query->latest()->paginate(15));
    }

    public function store(StorePayoutRequest $request): JsonResponse
    {
        $this->authorize('create', Payout::class);

        $customer = Customer::query()->findOrFail($request->integer('customer_id'));

        if ($customer->user_id !== $request->user()->id) {
            abort(403);
        }

        try {
            $payout = $this->payoutService->create(
                customer: $customer,
                amount: $request->integer('amount'),
                currency: strtoupper($request->string('currency')->toString() ?: 'USD'),
                simulateFailure: $request->boolean('simulate_failure')
            );
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return (new PayoutResource($payout))->response()->setStatusCode(201);
    }

    public function show(Payout $payout): PayoutResource
    {
        $this->authorize('view', $payout);

        return new PayoutResource($payout);
    }
}
