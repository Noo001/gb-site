<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Store1CStocksSyncRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array'],
            'items.*.offer_external_id' => ['required', 'string', 'max:255'],
            'items.*.name' => ['nullable', 'string', 'max:1000'],
            'items.*.store_external_id' => ['nullable', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0'],
        ];
    }
}
