<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PcDemoPart;
use App\Models\Price;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PcConfiguratorController extends Controller
{
    /**
     * Страница конфигуратора ПК (пошаговый визард сборки).
     */
    public function index(Request $request)
    {
        $assemblyPrices = Setting::get('pc_assembly_prices');
        if (is_string($assemblyPrices)) {
            $assemblyPrices = json_decode($assemblyPrices, true);
        }
        if (empty($assemblyPrices) || ! is_array($assemblyPrices)) {
            $assemblyPrices = [
                ['name' => 'Lite', 'min' => 0, 'max' => 60000, 'price' => 4500],
                ['name' => 'Standart', 'min' => 60000, 'max' => 140000, 'price' => 6000],
                ['name' => 'Gaming', 'min' => 140000, 'max' => 300000, 'price' => 8000],
                ['name' => 'Ultra', 'min' => 300000, 'max' => null, 'price' => 10000],
            ];
        }

        return view('pc.configurator', [
            'city' => $request->query('city'),
            'demo' => Setting::get('pc_demo_mode') === '1',
            'assemblyPrices' => $assemblyPrices,
        ]);
    }

    /**
     * Приём заявки на сборку ПК (без корзины).
     */
    public function store(Request $request): JsonResponse
    {
        $isDemo = Setting::get('pc_demo_mode') === '1';

        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:50'],
            'customer_city' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', $isDemo ? 'exists:pc_demo_parts,id' : 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:99'],
            'assembly' => ['nullable', 'boolean'],
            'assembly_package' => ['nullable', 'string', 'max:50'],
            'windows_install' => ['nullable', 'boolean'],
        ]);

        // Демо-режим: позиции из pc_demo_parts (product_id заказа = null, имя/цена из демо).
        if ($isDemo) {
            $demoParts = PcDemoPart::query()
                ->whereIn('id', collect($data['items'])->pluck('product_id'))
                ->get()
                ->keyBy('id');

            $order = DB::transaction(function () use ($request, $data, $demoParts) {
                $order = Order::create([
                    'user_id' => $request->user()?->id,
                    'status' => Order::STATUS_PENDING,
                    'customer_name' => $data['customer_name'],
                    'customer_phone' => $data['customer_phone'],
                    'customer_city' => $data['customer_city'] ?? null,
                ]);

                $orderTotal = 0;
                $commentLines = ['Сборка ПК (ДЕМО):'];

                foreach ($data['items'] as $item) {
                    $part = $demoParts->get($item['product_id']);
                    $price = $part ? (float) $part->price : null;
                    $itemTotal = $price !== null ? $price * $item['quantity'] : null;
                    $orderTotal += $itemTotal ?? 0;

                    $order->items()->create([
                        'product_id' => null,
                        'offer_id' => null,
                        'product_name' => $part?->name ?? "Демо-деталь #{$item['product_id']}",
                        'offer_name' => null,
                        'quantity' => $item['quantity'],
                        'price' => $price,
                        'total' => $itemTotal,
                    ]);

                    $commentLines[] = sprintf('- %s × %d', $part?->name ?? "Демо-деталь #{$item['product_id']}", $item['quantity']);
                }

                $assemblyPrice = $this->appendAssembly($commentLines, $data, $orderTotal, function ($item) use ($order) {
                    $order->items()->create($item);
                });
                $orderTotal += $assemblyPrice;

                $order->update([
                    'total' => $orderTotal,
                    'customer_comment' => implode("\n", $commentLines),
                ]);

                return $order;
            });

            return response()->json([
                'success' => true,
                'order_id' => $order->id,
                'message' => 'Заявка на сборку принята. Менеджер свяжется с вами.',
            ], 201);
        }

        $products = Product::query()
            ->with('offers')
            ->whereIn('id', collect($data['items'])->pluck('product_id'))
            ->get()
            ->keyBy('id');

        $order = DB::transaction(function () use ($request, $data, $products) {
            $order = Order::create([
                'user_id' => $request->user()?->id,
                'status' => Order::STATUS_PENDING,
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'customer_city' => $data['customer_city'] ?? null,
            ]);

            $orderTotal = 0;
            $hasPrices = true;
            $commentLines = ['Сборка ПК:'];

            foreach ($data['items'] as $item) {
                $product = $products->get($item['product_id']);
                $offer = $product?->defaultOffer();

                $price = null;
                if ($offer) {
                    $price = Price::where('offer_id', $offer->id)->value('price');
                }
                if ($price === null && $product) {
                    $price = Price::whereHas('offer', fn ($q) => $q->where('product_id', $product->id))
                        ->value('price');
                }

                $itemTotal = $price !== null ? $price * $item['quantity'] : null;
                if ($itemTotal === null) {
                    $hasPrices = false;
                } else {
                    $orderTotal += $itemTotal;
                }

                $order->items()->create([
                    'product_id' => $product?->id,
                    'offer_id' => $offer?->id,
                    'product_name' => $product?->name ?? 'Товар',
                    'offer_name' => $offer?->name,
                    'quantity' => $item['quantity'],
                    'price' => $price,
                    'total' => $itemTotal,
                ]);

                $commentLines[] = sprintf('- %s × %d', $product?->name ?? "Товар #{$item['product_id']}", $item['quantity']);
            }

            $assemblyPrice = $this->appendAssembly($commentLines, $data, $orderTotal, function ($item) use ($order) {
                $order->items()->create($item);
            });
            $orderTotal += $assemblyPrice;

            $order->update([
                'total' => $hasPrices ? $orderTotal : null,
                'customer_comment' => implode("\n", $commentLines),
            ]);

            return $order;
        });

        return response()->json([
            'success' => true,
            'order_id' => $order->id,
            'message' => 'Заявка на сборку принята. Менеджер свяжется с вами.',
        ], 201);
    }

    /**
     * Заявка на подбор ПК менеджером (когда автоподбор не справился).
     */
    public function storeManagerRequest(Request $request): JsonResponse
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:50'],
            'customer_city' => ['nullable', 'string', 'max:255'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'purpose' => ['nullable', 'string', 'max:255'],
            'wishes' => ['nullable', 'string', 'max:2000'],
        ]);

        $commentLines = ['Заявка на подбор ПК менеджером:'];

        if (! empty($data['budget'])) {
            $commentLines[] = sprintf('Бюджет: %s ₽', number_format((float) $data['budget'], 0, '.', ' '));
        }
        if (! empty($data['purpose'])) {
            $commentLines[] = sprintf('Цель: %s', $data['purpose']);
        }
        if (! empty($data['wishes'])) {
            $commentLines[] = sprintf('Пожелания: %s', $data['wishes']);
        }

        $order = Order::create([
            'user_id' => $request->user()?->id,
            'status' => Order::STATUS_PENDING,
            'customer_name' => $data['customer_name'],
            'customer_phone' => $data['customer_phone'],
            'customer_city' => $data['customer_city'] ?? null,
            'total' => null,
            'customer_comment' => implode("\n", $commentLines),
        ]);

        return response()->json([
            'success' => true,
            'order_id' => $order->id,
            'message' => 'Заявка принята. Менеджер свяжется с вами и подберёт конфигурацию.',
        ], 201);
    }

    /**
     * Возвращает цену сборки по сумме комплектующих из настроек.
     */
    private function assemblyPrice(float $partsTotal, ?string &$packageName = null): float
    {
        $prices = Setting::get('pc_assembly_prices');
        if (is_string($prices)) {
            $prices = json_decode($prices, true);
        }
        if (empty($prices) || ! is_array($prices)) {
            $prices = [
                ['name' => 'Lite', 'min' => 0, 'max' => 60000, 'price' => 4500],
                ['name' => 'Standart', 'min' => 60000, 'max' => 140000, 'price' => 6000],
                ['name' => 'Gaming', 'min' => 140000, 'max' => 300000, 'price' => 8000],
                ['name' => 'Ultra', 'min' => 300000, 'max' => null, 'price' => 10000],
            ];
        }

        foreach ($prices as $tier) {
            $min = (float) ($tier['min'] ?? 0);
            $max = $tier['max'] !== null && $tier['max'] !== '' ? (float) $tier['max'] : PHP_FLOAT_MAX;
            if ($partsTotal >= $min && $partsTotal < $max) {
                $packageName = $tier['name'] ?? 'Не указан';
                return (float) ($tier['price'] ?? 0);
            }
        }

        $packageName = null;
        return 0;
    }

    /**
     * Добавляет услугу сборки ПК к комментарию и позициям заказа.
     */
    private function appendAssembly(array &$commentLines, array $data, float $partsTotal, callable $createItem): float
    {
        if (empty($data['assembly'])) {
            return 0;
        }

        $packageName = $data['assembly_package'] ?? null;
        $price = $this->assemblyPrice($partsTotal, $packageName);

        $commentLines[] = '';
        $commentLines[] = 'Услуга сборки ПК:';
        $commentLines[] = '- Тариф: ' . ($packageName ?? 'Не указан');
        $commentLines[] = '- Стоимость: ' . number_format($price, 0, '.', ' ') . ' ₽';
        $commentLines[] = '- Установка Windows: ' . (! empty($data['windows_install']) ? 'да' : 'нет');
        $commentLines[] = '- Установка Microsoft Office: в подарок';

        if ($price > 0) {
            $createItem([
                'product_id' => null,
                'offer_id' => null,
                'product_name' => 'Сборка ПК (' . ($packageName ?? 'Не указан') . ')',
                'offer_name' => null,
                'quantity' => 1,
                'price' => $price,
                'total' => $price,
            ]);
        }

        return $price;
    }
}
