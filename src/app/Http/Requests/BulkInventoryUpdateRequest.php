<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkInventoryUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array'],
            'items.*.sku' => ['required', 'string', 'exists:product_variants,sku'],
            'items.*.quantity' => ['required', 'integer', 'min:0'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.is_available' => ['sometimes', 'boolean'],
        ];
    }
}