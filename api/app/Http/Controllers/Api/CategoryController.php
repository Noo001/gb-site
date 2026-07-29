<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $roots = Category::query()
            ->whereNull('parent_id')
            ->forCatalog()
            ->orderBy('sort')
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $roots->map(fn (Category $c) => [
                'id' => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
                'full_path' => $c->full_path,
                'url' => $c->url,
                'image' => null,
                'children' => [],
            ]),
        ]);
    }

    public function show(Request $request, string $path): JsonResponse
    {
        $path = trim($path, '/');

        if (! str_starts_with($path, 'catalog/') && ! str_starts_with($path, 'brands/')) {
            $path = 'catalog/'.$path;
        }

        $path = '/'.$path.'/';

        $category = Category::query()
            ->where('url', $path)
            ->orWhere('full_path', $path)
            ->with([
                'parent',
                'children' => fn ($q) => $q->forCatalog()->orderBy('sort')->orderBy('name'),
            ])
            ->first();

        if (! $category || $category->isService()) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        return response()->json([
            'data' => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'full_path' => $category->full_path,
                'url' => $category->url,
                'image' => null,
                'parent' => $category->parent ? [
                    'id' => $category->parent->id,
                    'name' => $category->parent->name,
                    'url' => $category->parent->url,
                ] : null,
                'children' => $category->children->map(fn (Category $c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'slug' => $c->slug,
                    'full_path' => $c->full_path,
                    'url' => $c->url,
                    'image' => $this->categoryImage($c),
                ]),
            ],
        ]);
    }

    public function products(Request $request, string $path): JsonResponse
    {
        $path = '/catalog/'.trim($path, '/').'/';

        $category = Category::query()
            ->where('url', $path)
            ->orWhere('full_path', $path)
            ->first();

        if (! $category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $categoryIds = $this->descendantIds($category);

        $products = Product::query()
            ->where('is_active', true)
            ->whereIn('category_id', $categoryIds)
            ->with([
                'media',
                'offers' => fn ($q) => $q->where('is_active', true)->orderBy('sort'),
                'offers.prices',
                'offers.stocks',
            ])
            ->orderBy('name')
            ->paginate($request->integer('per_page', 24));

        $categoryImage = $this->categoryImage($category);

        $products->getCollection()->transform(fn (\App\Models\Product $p) => [
            'id' => $p->id,
            'name' => $p->name,
            'slug' => $p->slug,
            'sku' => $p->sku,
            'brand' => $p->brand,
            'description' => $p->description,
            'url' => $p->url,
            'category_id' => $p->category_id,
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'url' => $category->url,
                'image' => $categoryImage,
            ],
            'images' => $p->getMedia('images')->map(fn ($m) => $m->getUrl())->values(),
            'price' => $p->minPrice(),
            'stock' => $p->totalStock(),
        ]);

        return response()->json($products);
    }

    private function treeNode(Category $category): array
    {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'full_path' => $category->full_path,
            'url' => $category->url,
            'image' => null,
            'children' => $category->children
                ? $category->children->map(fn (Category $c) => $this->treeNode($c))
                : [],
        ];
    }

    private function categoryImage(Category $category): ?string
    {
        $url = $category->getFirstMediaUrl('image');
        if ($url) {
            return $url;
        }

        $categoryIds = $this->descendantIds($category);

        $product = Product::query()
            ->where('is_active', true)
            ->whereIn('category_id', $categoryIds)
            ->whereHas('media')
            ->with('media')
            ->first();

        return $product?->getFirstMediaUrl('images');
    }

    private function descendantIds(Category $category): array
    {
        $rows = \DB::select("
            WITH RECURSIVE tree AS (
                SELECT id FROM categories WHERE id = ?
                UNION ALL
                SELECT c.id FROM categories c JOIN tree t ON c.parent_id = t.id
            )
            SELECT id FROM tree
        ", [$category->id]);

        return array_map(fn ($r) => (int) $r->id, $rows);
    }
}
