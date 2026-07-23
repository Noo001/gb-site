<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Price;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $orders = $request->user()
            ->orders()
            ->withCount('items')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Order $order) => $this->resource($order));

        return response()->json(['data' => $orders]);
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        $this->authorizeOrder($request, $order);

        return response()->json([
            'data' => $this->resource($order, true),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:50'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_city' => ['nullable', 'string', 'max:255'],
            'customer_comment' => ['nullable', 'string', 'max:5000'],
        ]);

        $cartItems = $this->cartQuery($request)
            ->with(['product', 'offer'])
            ->get();

        if ($cartItems->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => ['Корзина пуста. Добавьте товары перед оформлением заказа.'],
            ]);
        }

        $user = $request->user();
        $sessionId = $request->attributes->get('cart_session_id');

        $order = DB::transaction(function () use ($request, $data, $user, $sessionId, $cartItems) {
            $order = Order::create([
                'user_id' => $user?->id,
                'session_id' => $user ? null : $sessionId,
                'status' => Order::STATUS_PENDING,
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'customer_email' => $data['customer_email'] ?? null,
                'customer_city' => $data['customer_city'] ?? null,
                'customer_comment' => $data['customer_comment'] ?? null,
            ]);

            $orderTotal = 0;
            $hasPrices = true;

            foreach ($cartItems as $cartItem) {
                $product = $cartItem->product;
                $offer = $cartItem->offer;

                $price = null;
                if ($offer) {
                    $price = Price::where('offer_id', $offer->id)->value('price');
                }

                if ($price === null && $product) {
                    $price = Price::whereHas('offer', fn ($q) => $q->where('product_id', $product->id))
                        ->value('price');
                }

                $itemTotal = $price !== null ? $price * $cartItem->quantity : null;
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
                    'quantity' => $cartItem->quantity,
                    'price' => $price,
                    'total' => $itemTotal,
                ]);
            }

            $order->update(['total' => $hasPrices ? $orderTotal : null]);

            $this->cartQuery($request)->delete();

            return $order;
        });

        return response()->json([
            'data' => $this->resource($order->fresh('items'), true),
            'message' => 'Заявка принята. Менеджер свяжется с вами.',
        ], 201);
    }

    private function cartQuery(Request $request)
    {
        $user = $request->user();

        if ($user) {
            return CartItem::where('user_id', $user->id);
        }

        return CartItem::where('session_id', $request->attributes->get('cart_session_id'));
    }

    private function authorizeOrder(Request $request, Order $order): void
    {
        $user = $request->user();

        if ($user && $order->user_id === $user->id) {
            return;
        }

        if (! $user && $order->session_id && $order->session_id === $request->attributes->get('cart_session_id')) {
            return;
        }

        throw ValidationException::withMessages(['order' => ['Доступ запрещён.']]);
    }

    private function resource(Order $order, bool $withItems = false): array
    {
        $data = [
            'id' => $order->id,
            'status' => $order->status,
            'status_label' => $order->statusLabel(),
            'customer_name' => $order->customer_name,
            'customer_phone' => $order->customer_phone,
            'customer_email' => $order->customer_email,
            'customer_city' => $order->customer_city,
            'customer_comment' => $order->customer_comment,
            'manager_comment' => $order->manager_comment,
            'total' => $order->total,
            'items_count' => $order->items_count ?? $order->items->sum('quantity'),
            'created_at' => $order->created_at?->toDateTimeString(),
        ];

        if ($withItems) {
            $data['items'] = $order->items->map(fn ($item) => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'offer_id' => $item->offer_id,
                'product_name' => $item->product_name,
                'offer_name' => $item->offer_name,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'total' => $item->total,
            ]);
        }

        return $data;
    }
}
