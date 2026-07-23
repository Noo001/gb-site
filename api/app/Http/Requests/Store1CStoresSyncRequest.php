<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Store1CStoresSyncRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array'],
            'items.*.external_id' => ['required', 'string', 'max:255'],
            'items.*.name' => ['nullable', 'string', 'max:255'],
            'items.*.city' => ['nullable', 'string', 'max:255'],
            'items.*.address' => ['nullable', 'string', 'max:500'],
            'items.*.is_active' => ['nullable', 'boolean'],
        ];
    }
}
