<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\WishlistItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class WishlistController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $items = $request->user()->wishlistItems()
            ->with('product.media')
            ->get()
            ->map(fn (WishlistItem $item) => [
                'id' => $item->id,
                'product' => [
                    'id' => $item->product->id,
                    'name' => $item->product->name,
                    'slug' => $item->product->slug,
                    'url' => $item->product->url,
                    'image' => $item->product->getFirstMediaUrl('images'),
                ],
                'offer' => $item->offer ? ['id' => $item->offer->id, 'name' => $item->offer->name] : null,
            ]);

        return response()->json([
            'data' => $items,
            'count' => $items->count(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => ['required_without:offer_id', 'integer', 'exists:products,id'],
            'offer_id' => ['required_without:product_id', 'integer', 'exists:offers,id'],
        ]);

        $productId = $data['product_id'] ?? null;
        $offerId = $data['offer_id'] ?? null;

        if ($offerId && ! $productId) {
            $offer = Offer::find($offerId);
            $productId = $offer?->product_id;
        }

        $request->user()->wishlistItems()->firstOrCreate([
            'product_id' => $productId,
            'offer_id' => $offerId,
        ]);

        return $this->index($request);
    }

    public function destroy(Request $request, WishlistItem $item): JsonResponse
    {
        if ($item->user_id !== $request->user()->id) {
            throw ValidationException::withMessages(['item' => ['Доступ запрещён.']]);
        }

        $item->delete();

        return $this->index($request);
    }
}
