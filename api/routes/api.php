<?php

use App\Http\Controllers\Api\SeoController;
use Illuminate\Support\Facades\Route;

Route::get('/seo', [SeoController::class, 'show']);

Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});
