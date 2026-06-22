<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $roots = Category::query()
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('sort')
            ->orderBy('name')
            ->with([
                'media',
                'children' => fn ($q) => $q->where('is_active', true)->orderBy('sort')->orderBy('name')->with('media'),
            ])
            ->get();

        return response()->json([
            'data' => $roots->map(fn (Category $c) => $this->treeNode($c)),
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
                'children' => fn ($q) => $q->where('is_active', true)->orderBy('sort')->orderBy('name'),
            ])
            ->first();

        if (! $category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        return response()->json([
            'data' => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'full_path' => $category->full_path,
                'url' => $category->url,
                'image' => $this->categoryImage($category),
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

        $products = $category->products()
            ->where('is_active', true)
            ->with('media')
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
            'image' => $this->categoryImage($category),
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

        $product = $category->products()
            ->where('is_active', true)
            ->whereHas('media')
            ->with('media')
            ->first();

        return $product?->getFirstMediaUrl('images');
    }
}
