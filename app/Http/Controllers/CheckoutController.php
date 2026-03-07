<?php

namespace App\Http\Controllers;

use App\Actions\ConfirmPaymentIntentAction;
use App\Actions\CreatePaymentIntentAction;
use App\Http\Requests\ConfirmPaymentIntentRequest;
use App\Models\Customer;
use App\Models\PaymentIntent;
use App\Models\Product;
use App\Services\AppSettingsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(
        private CreatePaymentIntentAction $createPaymentIntentAction,
        private ConfirmPaymentIntentAction $confirmPaymentIntentAction,
        private AppSettingsService $appSettingsService
    ) {
    }

    public function checkout(Product $product): View
    {
        if (! (bool) $this->appSettingsService->get('allow_guest_checkout', true)) {
            abort(403, 'Guest checkout is disabled in settings.');
        }

        $customer = Customer::query()->firstOrCreate(
            ['email' => 'demo@local.test'],
            ['name' => 'Demo Customer']
        );

        $paymentIntent = $this->createPaymentIntentAction->execute([
            'customer_id' => $customer->id,
            'amount' => $product->price,
            'currency' => $product->currency,
            'metadata' => ['product_id' => $product->id],
        ]);

        return view('storefront.checkout', compact('product', 'paymentIntent'));
    }

    public function payment(PaymentIntent $intent): View
    {
        return view('storefront.payment', ['intent' => $intent]);
    }

    public function status(PaymentIntent $intent): JsonResponse
    {
        $redirectUrl = null;

        if ($intent->status->value === 'succeeded') {
            $redirectUrl = route('payment.success', ['intent' => $intent->id]);
        }

        if ($intent->status->value === 'failed') {
            $redirectUrl = route('payment.failed', ['intent' => $intent->id]);
        }

        return response()->json([
            'id' => $intent->id,
            'status' => $intent->status->value,
            'failure_code' => $intent->failure_code,
            'failure_message' => $intent->failure_message,
            'retry_count' => $intent->retry_count,
            'next_retry_at' => optional($intent->next_retry_at)?->toIso8601String(),
            'redirect_url' => $redirectUrl,
        ]);
    }

    public function confirm(ConfirmPaymentIntentRequest $request, PaymentIntent $intent): RedirectResponse
    {
        $updatedIntent = $this->confirmPaymentIntentAction->execute($intent, $request->validated());

        return redirect()->route('payment.show', ['intent' => $updatedIntent->id]);
    }

    public function success(Request $request): View
    {
        $intentId = $request->integer('intent');

        $intent = $intentId > 0
            ? PaymentIntent::query()->with(['order.product'])->find($intentId)
            : null;

        return view('storefront.success', ['intent' => $intent]);
    }

    public function failed(Request $request): View
    {
        $intentId = $request->integer('intent');

        $intent = $intentId > 0
            ? PaymentIntent::query()->find($intentId)
            : null;

        return view('storefront.failed', ['intent' => $intent]);
    }
}
