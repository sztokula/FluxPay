<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStorefrontSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'project_name' => ['required', 'string', 'max:80'],
            'support_email' => ['required', 'email', 'max:120'],
            'default_currency' => ['required', 'string', 'size:3'],
            'default_timezone' => ['required', 'string', 'max:80'],
            'allow_guest_checkout' => ['nullable', 'boolean'],
            'auto_finalize_orders' => ['nullable', 'boolean'],
            'enable_processing_watchdog' => ['nullable', 'boolean'],
            'high_value_review_threshold' => ['required', 'integer', 'min:1000'],
            'max_retry_attempts' => ['required', 'integer', 'min:1', 'max:4'],
            'api_rate_limit_per_minute' => ['required', 'integer', 'min:30', 'max:10000'],
            'payment_live_poll_interval_ms' => ['required', 'integer', 'min:1000', 'max:10000'],
            'checkout_default_test_card' => ['required', 'digits_between:13,19'],
            'maintenance_banner_enabled' => ['nullable', 'boolean'],
            'maintenance_message' => ['required', 'string', 'max:180'],
        ];
    }
}
