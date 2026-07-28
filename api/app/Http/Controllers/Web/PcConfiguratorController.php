<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Price;
use App\Models\Product;
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
        return view('pc.configurator', [
            'city' => $request->query('city'),
        ]);
    }

    /**
     * Приём заявки на сборку ПК (без корзины).
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:50'],
            'customer_city' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

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
}
