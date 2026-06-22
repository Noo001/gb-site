<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OfferController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Offer::query()
            ->where('is_active', true)
            ->with('product')
            ->orderBy('sort');

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->integer('product_id'));
        }

        if ($request->filled('sku')) {
            $query->where('sku', $request->input('sku'));
        }

        return response()->json(
            $query->paginate($request->integer('per_page', 24))
        );
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $offer = Offer::query()
            ->where('is_active', true)
            ->with(['product', 'prices', 'stocks'])
            ->find($id);

        if (! $offer) {
            return response()->json(['message' => 'Offer not found'], 404);
        }

        return response()->json([
            'data' => [
                'id' => $offer->id,
                'name' => $offer->name,
                'sku' => $offer->sku,
                'barcode' => $offer->barcode,
                'product' => $offer->product ? [
                    'id' => $offer->product->id,
                    'name' => $offer->product->name,
                    'slug' => $offer->product->slug,
                ] : null,
                'prices' => $offer->prices,
                'stocks' => $offer->stocks,
            ],
        ]);
    }
}
