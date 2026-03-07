<?php

use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\EventLogController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\LedgerEntryController;
use App\Http\Controllers\Api\PaymentIntentController;
use App\Http\Controllers\Api\PayoutController;
use App\Http\Controllers\Api\SystemHealthController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\WebhookEndpointController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['throttle:api', 'auth:sanctum'])->group(function (): void {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::apiResource('customers', CustomerController::class);
    Route::apiResource('payment-intents', PaymentIntentController::class)->only(['index', 'store', 'show']);
    Route::post('payment-intents/{paymentIntent}/confirm', [PaymentIntentController::class, 'confirm']);
    Route::post('payment-intents/{paymentIntent}/cancel', [PaymentIntentController::class, 'cancel']);
    Route::apiResource('subscriptions', SubscriptionController::class)->only(['index', 'store', 'show', 'destroy']);
    Route::apiResource('webhooks', WebhookEndpointController::class)->parameters(['webhooks' => 'webhook']);
    Route::apiResource('invoices', InvoiceController::class)->only(['index', 'store', 'show']);
    Route::apiResource('payouts', PayoutController::class)->only(['index', 'store', 'show']);
    Route::apiResource('ledger-entries', LedgerEntryController::class)->only(['index', 'show']);
    Route::apiResource('event-logs', EventLogController::class)->only(['index', 'show']);
    Route::get('system/health', SystemHealthController::class);
});
