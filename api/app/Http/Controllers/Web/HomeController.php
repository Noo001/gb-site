<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $categories = app(CategoryController::class)->index($request)->getData()->data;

        $productsResponse = app(ProductController::class)->index(
            new Request(['per_page' => 12])
        )->getData();
        $products = $productsResponse->data;

        $brandSlugs = collect($categories)
            ->filter(fn ($c) => str_starts_with($c->full_path ?? '', '/brands/'))
            ->pluck('slug')
            ->all();

        return view('home', compact('categories', 'products', 'brandSlugs'));
    }
}
