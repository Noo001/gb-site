<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Store1CCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'external_id' => ['required', 'string', 'max:255'],
            'parent_external_id' => ['nullable', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
            'sort' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
