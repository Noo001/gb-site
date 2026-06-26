<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CityController;
use App\Http\Controllers\Api\OneCController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\OfferController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SeoController;
use App\Http\Controllers\Api\SocialAuthController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Middleware\EnsureCartSession;
use Illuminate\Support\Facades\Route;

Route::get('/seo', [SeoController::class, 'show']);

Route::get('/city/detect', [CityController::class, 'detect']);

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{path}/products', [CategoryController::class, 'products'])
    ->where('path', '.*');
Route::get('/categories/{path}', [CategoryController::class, 'show'])
    ->where('path', '.*');

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{slug}', [ProductController::class, 'show']);

Route::get('/offers', [OfferController::class, 'index']);
Route::get('/offers/{id}', [OfferController::class, 'show']);

// Auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::get('/auth/{provider}', [SocialAuthController::class, 'redirect'])
    ->where('provider', 'yandex|vkontakte|vk');
Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])
    ->where('provider', 'yandex|vkontakte|vk');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::post('/wishlist', [WishlistController::class, 'store']);
    Route::delete('/wishlist/{item}', [WishlistController::class, 'destroy']);

    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
});

// Cart (guest + auth)
Route::middleware(EnsureCartSession::class)->group(function () {
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart/items', [CartController::class, 'store']);
    Route::patch('/cart/items/{item}', [CartController::class, 'update']);
    Route::delete('/cart/items/{item}', [CartController::class, 'destroy']);
    Route::delete('/cart', [CartController::class, 'clear']);

    Route::post('/orders', [OrderController::class, 'store']);
});

// 1C integration
Route::middleware(['onec.api', 'onec.log'])->prefix('1c')->group(function () {
    Route::post('/products/sync', [OneCController::class, 'syncProducts']);
    Route::put('/products/sync', [OneCController::class, 'syncProducts']);
    Route::post('/prices/sync', [OneCController::class, 'syncPrices']);
    Route::put('/prices/sync', [OneCController::class, 'syncPrices']);
    Route::get('/products', [OneCController::class, 'listProducts']);
    Route::get('/products/{uuid_1c}', [OneCController::class, 'showProduct']);
    Route::post('/notify/price-changed', [OneCController::class, 'notifyPriceChanged']);
});
