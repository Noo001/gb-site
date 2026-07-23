<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Delete1CProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'external_id' => ['required', 'string', 'max:255'],
            'permanent' => ['nullable', 'boolean'],
        ];
    }
}
