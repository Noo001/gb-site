<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::query()
            ->where('is_active', true)
            ->with(['category.media', 'media'])
            ->orderBy('name');

        $perPage = $request->integer('per_page', 24);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        if ($request->filled('brand')) {
            $query->where('brand', $request->input('brand'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('sku', 'ilike', "%{$search}%")
                    ->orWhere('brand', 'ilike', "%{$search}%");
            });
        }

        if ($request->boolean('has_image')) {
            $query->whereHas('media');
        }

        $paginator = $query->paginate($perPage);

        $paginator->getCollection()->transform(fn (Product $p) => [
            'id' => $p->id,
            'name' => $p->name,
            'slug' => $p->slug,
            'sku' => $p->sku,
            'brand' => $p->brand,
            'description' => $p->description,
            'url' => $p->url,
            'category_id' => $p->category_id,
            'category' => $p->category ? [
                'id' => $p->category->id,
                'name' => $p->category->name,
                'url' => $p->category->url,
                'image' => $p->category->getFirstMediaUrl('image') ?: null,
            ] : null,
            'images' => $p->getMedia('images')->map(fn ($m) => $m->getUrl())->values(),
        ]);

        return response()->json($paginator);
    }

    public function show(Request $request, string $slug): JsonResponse
    {
        $product = Product::query()
            ->where('slug', $slug)
            ->orWhere('url', '/product/'.$slug.'/')
            ->with([
                'category.media',
                'media',
                'offers' => fn ($q) => $q->where('is_active', true)->orderBy('sort'),
            ])
            ->first();

        if (! $product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json([
            'data' => $this->productDetail($product),
        ]);
    }

    private function productDetail(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'sku' => $product->sku,
            'brand' => $product->brand,
            'description' => $product->description,
            'content' => $product->content,
            'warranty_months' => $product->warranty_months,
            'url' => $product->url,
            'category' => $product->category ? [
                'id' => $product->category->id,
                'name' => $product->category->name,
                'url' => $product->category->url,
                'image' => $product->category->getFirstMediaUrl('image') ?: null,
            ] : null,
            'images' => $product->getMedia('images')->map(fn ($m) => $m->getUrl()),
            'offers' => $product->offers->map(fn ($offer) => [
                'id' => $offer->id,
                'name' => $offer->name,
                'sku' => $offer->sku,
                'barcode' => $offer->barcode,
            ]),
        ];
    }
}
