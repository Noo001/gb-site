<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Store1CProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'external_id' => ['required', 'string', 'max:255'],
            'category_external_id' => ['nullable', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:1000'],
            'sku' => ['nullable', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:20000'],
            'is_active' => ['nullable', 'boolean'],
            'images_urls' => ['nullable', 'array'],
            'images_urls.*' => ['url'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'price_type' => ['nullable', 'string', 'max:100'],
            'currency' => ['nullable', 'string', 'max:3'],
            'quantity' => ['nullable', 'numeric', 'min:0'],
            'store_external_id' => ['nullable', 'string', 'max:255'],
            'offers' => ['nullable', 'array'],
            'offers.*.external_id' => ['required', 'string', 'max:255'],
            'offers.*.name' => ['required', 'string', 'max:1000'],
            'offers.*.sku' => ['nullable', 'string', 'max:255'],
            'offers.*.barcode' => ['nullable', 'string', 'max:255'],
            'offers.*.is_active' => ['nullable', 'boolean'],
            'offers.*.prices' => ['nullable', 'array'],
            'offers.*.prices.*.price_type' => ['nullable', 'string', 'max:100'],
            'offers.*.prices.*.price' => ['required', 'numeric', 'min:0'],
            'offers.*.prices.*.currency' => ['nullable', 'string', 'max:3'],
            'offers.*.stocks' => ['nullable', 'array'],
            'offers.*.stocks.*.store_external_id' => ['nullable', 'string', 'max:255'],
            'offers.*.stocks.*.quantity' => ['required', 'numeric', 'min:0'],
            'attributes' => ['nullable', 'array'],
            'attributes.*.name' => ['required', 'string', 'max:255'],
            'attributes.*.value' => ['required', 'string', 'max:2000'],
            'attributes.*.unit' => ['nullable', 'string', 'max:50'],
        ];
    }
}
