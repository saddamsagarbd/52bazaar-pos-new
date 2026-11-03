<?php

namespace App\Http\Requests\Outlet;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOutletRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rowId = $this->input('row_id');

        return [
            'name' => [
                'required',
                'string',
                Rule::unique('outlets', 'name')->ignore($rowId),
            ],
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('outlets', 'code')->ignore($rowId),
            ],
            'parent_id' => 'nullable|integer|exists:outlets,id',
            'address' => 'nullable|string',
            'row_id' => 'nullable|integer',
        ];
    }
}
