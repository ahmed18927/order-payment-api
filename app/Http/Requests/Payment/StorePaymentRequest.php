<?php

namespace App\Http\Requests\Payment;

use App\Payment\PaymentGatewayFactory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $supported = app(PaymentGatewayFactory::class)->supportedGateways();

        return [
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'gateway'  => ['required', 'string', Rule::in($supported)],
        ];
    }

    public function messages(): array
    {
        return [
            'gateway.in' => 'The selected gateway is not supported. Supported: paypal, credit_card.',
        ];
    }
}