<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function show(Request $request, ?string $path = null)
    {
        if (empty($path)) {
            return $this->index($request);
        }

        $categoryResponse = app(CategoryController::class)->show($request, $path);
        if ($categoryResponse->getStatusCode() === 404) {
            return redirect('/catalog' . ($request->filled('site_access') ? '?site_access=' . urlencode($request->query('site_access')) : ''));
        }
        $category = $categoryResponse->getData()->data;

        $productsResponse = app(CategoryController::class)->products($request, $path);
        $products = $productsResponse->getData();

        $breadcrumbs = $this->breadcrumbs($category);

        return view('catalog.show', compact('category', 'products', 'breadcrumbs'));
    }

    private function index(Request $request)
    {
        $categoryResponse = app(CategoryController::class)->index($request);
        $categories = $categoryResponse->getData()->data;

        $productsResponse = app(ProductController::class)->index(
            new Request(['per_page' => 24])
        )->getData();

        $breadcrumbs = [
            ['name' => 'Главная', 'url' => '/'],
            ['name' => 'Каталог', 'url' => '/catalog'],
        ];

        $products = $productsResponse;

        return view('catalog.index', compact('categories', 'products', 'breadcrumbs'));
    }

    private function breadcrumbs(object $category): array
    {
        $crumbs = [['name' => 'Главная', 'url' => '/']];

        if (!empty($category->parent)) {
            $crumbs[] = ['name' => $category->parent->name, 'url' => $category->parent->url];
        }

        $crumbs[] = ['name' => $category->name, 'url' => $category->url];

        return $crumbs;
    }
}
