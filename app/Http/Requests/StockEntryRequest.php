<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StockEntryRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.outlet_id' => 'required|integer|exists:outlets,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.purchase_rate' => 'required|numeric|min:0',
            'items.*.selling_rate' => 'required|numeric|min:0',
            'items.*.expire_date' => 'nullable|date',
            'items.*.purchase_date' => 'required|date',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $items = $this->input('items', []);

            foreach ($items as $index => $item) {
                $purchase       = $item['purchase_rate'] ?? 0;
                $selling        = $item['selling_rate'] ?? 0;
                $purchaseDate   = $item['purchase_date'] ?? '';

                // Jodi selling rate purchase rate er cheye kom hoy
                if ($selling < $purchase) {
                    $validator->errors()->add(
                        "items.{$index}.selling_rate", 
                        "Row #" . ($index + 1) . ": Selling rate cannot be less than purchase rate!"
                    );
                }

                if(!empty($purchaseDate) && $purchaseDate > Carbon::now() ){
                    $validator->errors()->add(
                        "items.{$index}.purchase_date", 
                        "Row #" . ($index + 1) . ": Purchase date can not be future date!"
                    );
                }
            }
        });
    }
}
