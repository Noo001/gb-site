<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Store1CBulkSyncRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'categories' => ['nullable', 'array'],
            'categories.*.external_id' => ['required', 'string', 'max:255'],
            'categories.*.parent_external_id' => ['nullable', 'string', 'max:255'],
            'categories.*.name' => ['required', 'string', 'max:500'],
            'categories.*.is_active' => ['nullable', 'boolean'],
            'categories.*.sort' => ['nullable', 'integer', 'min:0'],

            'stores' => ['nullable', 'array'],
            'stores.*.external_id' => ['required', 'string', 'max:255'],
            'stores.*.name' => ['required', 'string', 'max:500'],
            'stores.*.city' => ['nullable', 'string', 'max:255'],
            'stores.*.address' => ['nullable', 'string', 'max:1000'],
            'stores.*.is_active' => ['nullable', 'boolean'],
            'stores.*.sort' => ['nullable', 'integer', 'min:0'],

            'products' => ['nullable', 'array'],
            'products.*.external_id' => ['required', 'string', 'max:255'],
            'products.*.category_external_id' => ['nullable', 'string', 'max:255'],
            'products.*.name' => ['required', 'string', 'max:1000'],
            'products.*.sku' => ['nullable', 'string', 'max:255'],
            'products.*.brand' => ['nullable', 'string', 'max:255'],
            'products.*.description' => ['nullable', 'string', 'max:20000'],
            'products.*.is_active' => ['nullable', 'boolean'],
            'products.*.images_urls' => ['nullable', 'array'],
            'products.*.images_urls.*' => ['url'],

            'products.*.offers' => ['nullable', 'array'],
            'products.*.offers.*.external_id' => ['required', 'string', 'max:255'],
            'products.*.offers.*.name' => ['required', 'string', 'max:1000'],
            'products.*.offers.*.sku' => ['nullable', 'string', 'max:255'],
            'products.*.offers.*.barcode' => ['nullable', 'string', 'max:255'],
            'products.*.offers.*.is_active' => ['nullable', 'boolean'],

            'products.*.offers.*.prices' => ['nullable', 'array'],
            'products.*.offers.*.prices.*.price_type' => ['nullable', 'string', 'max:100'],
            'products.*.offers.*.prices.*.price' => ['required', 'numeric', 'min:0'],
            'products.*.offers.*.prices.*.currency' => ['nullable', 'string', 'max:3'],

            'products.*.offers.*.stocks' => ['nullable', 'array'],
            'products.*.offers.*.stocks.*.store_external_id' => ['nullable', 'string', 'max:255'],
            'products.*.offers.*.stocks.*.quantity' => ['required', 'numeric', 'min:0'],

            'products.*.attributes' => ['nullable', 'array'],
            'products.*.attributes.*.name' => ['required', 'string', 'max:255'],
            'products.*.attributes.*.value' => ['required', 'string', 'max:2000'],
            'products.*.attributes.*.unit' => ['nullable', 'string', 'max:50'],
        ];
    }
}
