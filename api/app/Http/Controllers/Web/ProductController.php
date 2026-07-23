<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Api\ProductController as ApiProductController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function show(Request $request, string $slug)
    {
        $response = app(ApiProductController::class)->show($request, $slug);
        if ($response->getStatusCode() === 404) {
            abort(404);
        }

        $product = $response->getData()->data;

        $breadcrumbs = [
            ['name' => 'Главная', 'url' => '/'],
        ];

        if (!empty($product->category)) {
            $breadcrumbs[] = ['name' => $product->category->name, 'url' => $product->category->url];
        }

        $breadcrumbs[] = ['name' => $product->name, 'url' => $product->url];

        return view('product.show', compact('product', 'breadcrumbs'));
    }
}
