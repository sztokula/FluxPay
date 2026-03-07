<?php

namespace App\Http\Controllers;

use App\Actions\ConfirmPaymentIntentAction;
use App\Actions\CreatePaymentIntentAction;
use App\Http\Requests\RunStorefrontActionRequest;
use App\Http\Requests\UpdateStorefrontSettingsRequest;
use App\Models\Customer;
use App\Models\Product;
use App\Services\AppSettingsService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class StorefrontSettingsController extends Controller
{
    public function __construct(
        private AppSettingsService $settings,
        private CreatePaymentIntentAction $createPaymentIntentAction,
        private ConfirmPaymentIntentAction $confirmPaymentIntentAction
    ) {
    }

    public function show(): View
    {
        $defaults = $this->defaultSettings();
        $current = array_merge($defaults, $this->settings->all());

        return view('storefront.dashboard.settings', [
            'settings' => $current,
        ]);
    }

    public function update(UpdateStorefrontSettingsRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $this->settings->setMany([
            'project_name' => $validated['project_name'],
            'support_email' => $validated['support_email'],
            'default_currency' => strtoupper($validated['default_currency']),
            'default_timezone' => $validated['default_timezone'],
            'allow_guest_checkout' => (bool) ($validated['allow_guest_checkout'] ?? false),
            'auto_finalize_orders' => (bool) ($validated['auto_finalize_orders'] ?? false),
            'enable_processing_watchdog' => (bool) ($validated['enable_processing_watchdog'] ?? false),
            'high_value_review_threshold' => (int) $validated['high_value_review_threshold'],
            'max_retry_attempts' => (int) $validated['max_retry_attempts'],
            'api_rate_limit_per_minute' => (int) $validated['api_rate_limit_per_minute'],
            'payment_live_poll_interval_ms' => (int) $validated['payment_live_poll_interval_ms'],
            'checkout_default_test_card' => $validated['checkout_default_test_card'],
            'maintenance_banner_enabled' => (bool) ($validated['maintenance_banner_enabled'] ?? false),
            'maintenance_message' => $validated['maintenance_message'],
        ]);

        return redirect()
            ->route('dashboard.settings')
            ->with('settings_saved', true);
    }

    public function runAction(RunStorefrontActionRequest $request): RedirectResponse
    {
        $cardByAction = [
            'demo_success' => '4242424242424242',
            'demo_failure' => '4000000000000002',
            'demo_retry' => '4000000000009995',
        ];

        $action = $request->string('action')->toString();
        $cardNumber = $cardByAction[$action] ?? '4242424242424242';

        if ($action === 'watchdog_run') {
            Artisan::call('payment-intents:mark-stale-processing');

            return redirect()
                ->route('dashboard.settings')
                ->with('action_result', trim((string) Artisan::output()));
        }

        if ($action === 'demo_pack') {
            $created = [];
            foreach (['demo_success', 'demo_failure', 'demo_retry'] as $packAction) {
                $created[] = $this->runDemoPayment($cardByAction[$packAction], 'demo@local.test');
            }

            return redirect()
                ->route('dashboard.settings')
                ->with('action_result', 'Created demo pack intents: #'.implode(', #', $created));
        }

        if ($action === 'demo_fraud_review') {
            $threshold = (int) $this->settings->get('high_value_review_threshold', 100000);
            $intentId = $this->runDemoPayment('4242424242424242', 'fraud-review-'.now()->timestamp.'@local.test', $threshold + 100);

            return redirect()
                ->route('dashboard.settings')
                ->with('action_result', 'Created high-value demo intent #'.$intentId.' for fraud-review scenario.');
        }

        $intentId = $this->runDemoPayment($cardNumber, 'demo@local.test');

        return redirect()
            ->route('dashboard.settings')
            ->with('action_result', 'Created demo payment intent #'.$intentId.' ('.$action.').');
    }

    /**
     * @return array<string, mixed>
     */
    private function defaultSettings(): array
    {
        return [
            'project_name' => config('app.name', 'FluxPay'),
            'support_email' => 'support@local.test',
            'default_currency' => 'USD',
            'default_timezone' => config('app.timezone', 'UTC'),
            'allow_guest_checkout' => true,
            'auto_finalize_orders' => true,
            'enable_processing_watchdog' => true,
            'high_value_review_threshold' => 100000,
            'max_retry_attempts' => 4,
            'api_rate_limit_per_minute' => 240,
            'payment_live_poll_interval_ms' => 2000,
            'checkout_default_test_card' => '4242424242424242',
            'maintenance_banner_enabled' => false,
            'maintenance_message' => 'Maintenance mode is enabled. Some operations may be delayed.',
        ];
    }

    private function runDemoPayment(string $cardNumber, string $customerEmail, ?int $amount = null): int
    {
        $customer = Customer::query()->firstOrCreate(
            ['email' => $customerEmail],
            ['name' => 'Demo Customer']
        );

        $product = Product::query()->where('is_active', true)->first()
            ?? Product::query()->create([
                'name' => 'Starter Plan',
                'description' => 'Autogenerated product for demo scenarios.',
                'price' => 2900,
                'currency' => 'USD',
                'is_active' => true,
            ]);

        $intent = $this->createPaymentIntentAction->execute([
            'customer_id' => $customer->id,
            'amount' => $amount ?? $product->price,
            'currency' => $product->currency,
            'metadata' => ['product_id' => $product->id, 'source' => 'settings_demo_action'],
        ]);

        $this->confirmPaymentIntentAction->execute($intent, [
            'card_number' => $cardNumber,
        ]);

        return $intent->id;
    }
}
