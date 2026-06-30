<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Store1CPriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'offer_external_id' => ['required', 'string', 'max:255'],
            'price_type' => ['nullable', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:3'],
        ];
    }
}
