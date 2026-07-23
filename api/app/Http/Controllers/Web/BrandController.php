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
        $categories = app(CategoryController::class)->index($request)->getData()->data;
        $brands = array_filter($categories, fn ($c) => str_starts_with($c->full_path ?? '', '/brands/'));

        return view('brands.index', compact('brands'));
    }

    public function show(Request $request, string $slug)
    {
        $categories = app(CategoryController::class)->index($request)->getData()->data;
        $brand = null;

        foreach ($categories as $category) {
            foreach ($category->children ?? [] as $child) {
                if ($child->slug === $slug) {
                    $brand = $child;
                    break 2;
                }
            }
        }

        if (! $brand) {
            return redirect('/brands');
        }

        $products = app(ProductController::class)->index(
            new Request(['brand' => $brand->name, 'per_page' => 24])
        )->getData();

        $breadcrumbs = [
            ['name' => 'Главная', 'url' => '/'],
            ['name' => 'Бренды', 'url' => '/brands'],
            ['name' => $brand->name, 'url' => $brand->url],
        ];

        return view('brands.show', compact('brand', 'products', 'breadcrumbs'));
    }
}
