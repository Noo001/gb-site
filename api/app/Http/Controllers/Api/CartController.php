<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Offer;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $items = $this->query($request)
            ->with(['product.media', 'offer'])
            ->get()
            ->map(fn (CartItem $item) => $this->resource($item));

        return response()->json([
            'data' => $items,
            'count' => $items->sum('quantity'),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => ['required_without:offer_id', 'integer', 'exists:products,id'],
            'offer_id' => ['required_without:product_id', 'integer', 'exists:offers,id'],
            'quantity' => ['integer', 'min:1'],
        ]);

        $productId = $data['product_id'] ?? null;
        $offerId = $data['offer_id'] ?? null;
        $quantity = $data['quantity'] ?? 1;

        if ($offerId && ! $productId) {
            $offer = Offer::find($offerId);
            $productId = $offer?->product_id;
        }

        $existing = $this->query($request)
            ->where('product_id', $productId)
            ->where('offer_id', $offerId)
            ->first();

        if ($existing) {
            $existing->increment('quantity', $quantity);
        } else {
            CartItem::create([
                'user_id' => $request->user()?->id,
                'session_id' => $request->attributes->get('cart_session_id'),
                'product_id' => $productId,
                'offer_id' => $offerId,
                'quantity' => $quantity,
            ]);
        }

        return $this->index($request);
    }

    public function update(Request $request, CartItem $item): JsonResponse
    {
        $this->authorizeItem($request, $item);

        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $item->update(['quantity' => $data['quantity']]);

        return $this->index($request);
    }

    public function destroy(Request $request, CartItem $item): JsonResponse
    {
        $this->authorizeItem($request, $item);
        $item->delete();

        return $this->index($request);
    }

    public function clear(Request $request): JsonResponse
    {
        $this->query($request)->delete();

        return response()->json(['data' => [], 'count' => 0]);
    }

    private function query(Request $request)
    {
        $user = $request->user();

        if ($user) {
            return CartItem::where('user_id', $user->id);
        }

        return CartItem::where('session_id', $request->attributes->get('cart_session_id'));
    }

    private function authorizeItem(Request $request, CartItem $item): void
    {
        $user = $request->user();
        $sessionId = $request->attributes->get('cart_session_id');

        $allowed = ($user && $item->user_id === $user->id)
            || (! $user && $item->session_id === $sessionId);

        if (! $allowed) {
            throw ValidationException::withMessages(['item' => ['Доступ запрещён.']]);
        }
    }

    private function resource(CartItem $item): array
    {
        return [
            'id' => $item->id,
            'quantity' => $item->quantity,
            'product' => $item->product ? [
                'id' => $item->product->id,
                'name' => $item->product->name,
                'slug' => $item->product->slug,
                'url' => $item->product->url,
                'image' => $item->product->getFirstMediaUrl('images'),
            ] : null,
            'offer' => $item->offer ? [
                'id' => $item->offer->id,
                'name' => $item->offer->name,
            ] : null,
        ];
    }
}
