<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'customer_id' => 'required|exists:customers,id',
            'outlet_id' => 'required|exists:outlets,id',
            'payment_type' => 'required|string',
            'pay_amount' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.stock_id' => 'required|exists:stocks,id',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|numeric|min:0.1',
            'items.*.price' => 'required|numeric|min:0',
        ];
    }
}
