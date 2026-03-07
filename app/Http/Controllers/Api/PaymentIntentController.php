<?php

namespace App\Http\Controllers\Api;

use App\Actions\ConfirmPaymentIntentAction;
use App\Actions\CreatePaymentIntentAction;
use App\Enums\PaymentIntentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\ConfirmPaymentIntentRequest;
use App\Http\Requests\StorePaymentIntentRequest;
use App\Http\Resources\PaymentIntentResource;
use App\Models\Customer;
use App\Models\PaymentIntent;
use App\Services\WebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PaymentIntentController extends Controller
{
    public function __construct(
        private CreatePaymentIntentAction $createPaymentIntentAction,
        private ConfirmPaymentIntentAction $confirmPaymentIntentAction,
        private WebhookService $webhookService
    ) {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', PaymentIntent::class);

        $query = PaymentIntent::query()
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

        return PaymentIntentResource::collection($query->latest()->paginate(15));
    }

    public function store(StorePaymentIntentRequest $request): JsonResponse
    {
        $this->authorize('create', PaymentIntent::class);

        $customer = Customer::query()->findOrFail($request->integer('customer_id'));

        if ($customer->user_id !== $request->user()->id) {
            abort(403);
        }

        $paymentIntent = $this->createPaymentIntentAction->execute($request->validated());

        return (new PaymentIntentResource($paymentIntent))
            ->response()
            ->setStatusCode(201);
    }

    public function show(PaymentIntent $paymentIntent): PaymentIntentResource
    {
        $this->authorize('view', $paymentIntent);

        return new PaymentIntentResource($paymentIntent);
    }

    public function confirm(ConfirmPaymentIntentRequest $request, PaymentIntent $paymentIntent): PaymentIntentResource
    {
        $this->authorize('update', $paymentIntent);

        $paymentIntent = $this->confirmPaymentIntentAction->execute($paymentIntent, $request->validated());

        return new PaymentIntentResource($paymentIntent);
    }

    public function cancel(PaymentIntent $paymentIntent): PaymentIntentResource
    {
        $this->authorize('update', $paymentIntent);

        $paymentIntent = $this->confirmPaymentIntentAction->cancel($paymentIntent);

        if ($paymentIntent->status === PaymentIntentStatus::Canceled) {
            $this->webhookService->publish(
                'payment_intent.failed',
                'payment_intent',
                $paymentIntent->id,
                $paymentIntent->toArray()
            );
        }

        return new PaymentIntentResource($paymentIntent);
    }
}
