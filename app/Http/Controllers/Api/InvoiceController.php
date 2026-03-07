<?php

namespace App\Http\Controllers\Api;

use App\Actions\CreatePaymentIntentAction;
use App\Enums\InvoiceStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\Customer;
use App\Models\Invoice;
use App\Services\WebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class InvoiceController extends Controller
{
    public function __construct(
        private WebhookService $webhookService,
        private CreatePaymentIntentAction $createPaymentIntentAction
    ) {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Invoice::class);

        $query = Invoice::query()
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

        return InvoiceResource::collection($query->latest()->paginate(15));
    }

    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        $this->authorize('create', Invoice::class);

        $customer = Customer::query()->findOrFail($request->integer('customer_id'));

        if ($customer->user_id !== $request->user()->id) {
            abort(403);
        }

        $invoice = Invoice::query()->create([
            'customer_id' => $request->integer('customer_id'),
            'subscription_id' => $request->integer('subscription_id') ?: null,
            'amount_due' => $request->integer('amount_due'),
            'amount_paid' => 0,
            'currency' => strtoupper($request->string('currency')->toString() ?: 'USD'),
            'status' => InvoiceStatus::Open,
            'due_at' => $request->date('due_at'),
            'metadata' => $request->input('metadata'),
        ]);

        $this->webhookService->publish('invoice.created', 'invoice', $invoice->id, $invoice->toArray());

        $paymentIntent = $this->createPaymentIntentAction->execute([
            'customer_id' => $invoice->customer_id,
            'invoice_id' => $invoice->id,
            'amount' => $invoice->amount_due,
            'currency' => $invoice->currency,
        ]);

        return (new InvoiceResource($invoice))
            ->additional(['meta' => ['payment_intent_id' => $paymentIntent->id]])
            ->response()
            ->setStatusCode(201);
    }

    public function show(Invoice $invoice): InvoiceResource
    {
        $this->authorize('view', $invoice);

        return new InvoiceResource($invoice);
    }
}
