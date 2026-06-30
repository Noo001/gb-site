<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Store1CStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'offer_external_id' => ['required', 'string', 'max:255'],
            'store_external_id' => ['nullable', 'string', 'max:255'],
            'quantity' => ['required', 'numeric', 'min:0'],
        ];
    }
}
