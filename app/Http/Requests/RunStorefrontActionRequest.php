<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RunStorefrontActionRequest extends FormRequest
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
            'action' => ['required', 'in:demo_success,demo_failure,demo_retry,demo_pack,demo_fraud_review,watchdog_run'],
        ];
    }
}
