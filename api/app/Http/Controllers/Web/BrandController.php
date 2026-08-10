<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        $brands = \App\Models\Product::query()
            ->forCatalog()
            ->whereNotNull('brand')
            ->distinct()
            ->orderBy('brand')
            ->pluck('brand')
            ->map(fn (string $name) => (object) [
                'name' => $name,
                'slug' => \Illuminate\Support\Str::slug($name),
                'url' => '/brands/' . \Illuminate\Support\Str::slug($name),
            ])
            ->values();

        return view('brands.index', compact('brands'));
    }

    public function show(Request $request, string $slug)
    {
        $brands = \App\Models\Product::query()
            ->forCatalog()
            ->whereNotNull('brand')
            ->distinct()
            ->pluck('brand');

        $brandName = $brands->first(fn ($name) => \Illuminate\Support\Str::slug($name) === $slug);

        if (! $brandName) {
            return redirect('/brands' . ($request->filled('site_access') ? '?site_access=' . urlencode($request->query('site_access')) : ''));
        }

        $brand = (object) [
            'name' => $brandName,
            'slug' => $slug,
            'url' => '/brands/' . $slug,
        ];

        $products = app(ProductController::class)->index(
            new Request(['brand' => $brandName, 'per_page' => 24])
        )->getData();

        $breadcrumbs = [
            ['name' => 'Главная', 'url' => '/'],
            ['name' => 'Бренды', 'url' => '/brands'],
            ['name' => $brand->name, 'url' => $brand->url],
        ];

        return view('brands.show', compact('brand', 'products', 'breadcrumbs'));
    }
}
