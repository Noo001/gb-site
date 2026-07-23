<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Store1CPricesSyncRequest extends FormRequest
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
            'items.*.price_type' => ['nullable', 'string', 'max:100'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.currency' => ['nullable', 'string', 'max:3'],
        ];
    }
}
