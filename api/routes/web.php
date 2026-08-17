<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\CatalogController;
use App\Http\Controllers\Web\ProductController;
use App\Http\Controllers\Web\BrandController;
use App\Http\Controllers\Web\CartController;
use App\Http\Controllers\Web\CheckoutController;
use App\Http\Controllers\Web\PcConfiguratorController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\AccountController;
use App\Http\Controllers\Web\WishlistController;
use App\Http\Controllers\Web\PageController;
use App\Http\Controllers\Web\BlogController;
use App\Http\Controllers\Web\SalesController;
use App\Http\Controllers\Web\CaptchaController;
use App\Http\Controllers\Web\BonusController;
use App\Http\Controllers\Web\FranchiseController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/captcha', [CaptchaController::class, 'show'])->name('captcha');

Route::get('/catalog/{path?}', [CatalogController::class, 'show'])
    ->where('path', '.*')->name('catalog.show');

Route::get('/product/{slug}', [ProductController::class, 'show'])->name('product.show');

Route::get('/brands', [BrandController::class, 'index'])->name('brands.index');
Route::get('/brands/{slug}', [BrandController::class, 'show'])->name('brands.show');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/order/success', [CheckoutController::class, 'success'])->name('checkout.success');

Route::get('/pc', [PcConfiguratorController::class, 'index'])->name('pc.index');
Route::get('/pc/configurator', [PcConfiguratorController::class, 'index'])->name('pc.configurator');
Route::post('/pc/build', [PcConfiguratorController::class, 'store'])->name('pc.build');
Route::post('/pc/manager-request', [PcConfiguratorController::class, 'storeManagerRequest'])->name('pc.manager-request');

Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.store');
Route::get('/register', [AuthController::class, 'registerForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.store');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/e2e/login', [\App\Http\Controllers\Web\E2ELoginController::class, '__invoke'])
    ->name('e2e.login')
    ->middleware('signed');

Route::get('/e2e/captcha', [\App\Http\Controllers\Web\E2ECaptchaController::class, '__invoke'])
    ->name('e2e.captcha')
    ->middleware('signed');

Route::post('/access-check', [AuthController::class, 'accessCheck'])->name('access.check');

Route::middleware('auth')->group(function () {
    Route::get('/account', [AccountController::class, 'dashboard'])->name('account.dashboard');
    Route::get('/account/profile', [AccountController::class, 'profile'])->name('account.profile');
    Route::post('/account/profile', [AccountController::class, 'updateProfile'])->name('account.profile.update');
    Route::post('/account/password', [AccountController::class, 'updatePassword'])->name('account.password.update');
    Route::get('/account/orders', [AccountController::class, 'orders'])->name('account.orders');
    Route::get('/account/wishlist', [AccountController::class, 'wishlist'])->name('account.wishlist');
    Route::get('/account/bonuses', [BonusController::class, 'index'])->name('account.bonuses');
    Route::post('/account/bonuses/terms', [BonusController::class, 'acceptTerms'])->name('account.bonuses.terms');
    Route::post('/account/bonuses/daily', [BonusController::class, 'daily'])->name('account.bonuses.daily');
    Route::post('/account/bonuses/spin', [BonusController::class, 'spin'])->name('account.bonuses.spin');

    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
});

Route::get('/info/{slug}', [PageController::class, 'info'])->name('page.info');
Route::get('/company', [PageController::class, 'company'])->name('page.company');
Route::get('/contacts', [PageController::class, 'contacts'])->name('page.contacts');
Route::get('/stores', [PageController::class, 'stores'])->name('page.stores');
Route::get('/delivery', fn () => redirect()->route('page.info', ['slug' => 'delivery']));
Route::get('/payment', fn () => redirect()->route('page.info', ['slug' => 'payment']));
Route::get('/warranty', fn () => redirect()->route('page.info', ['slug' => 'warranty']));
Route::get('/installment', [PageController::class, 'installment'])->name('page.installment');
Route::get('/trade-in', [PageController::class, 'tradeIn'])->name('page.trade-in');
Route::get('/offer', [PageController::class, 'offer'])->name('page.offer');
Route::get('/privacy', [PageController::class, 'privacy'])->name('page.privacy');
Route::get('/review', [PageController::class, 'review'])->name('page.review');

Route::get('/blog', [BlogController::class, 'index'])->name('page.blog');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('page.blog.article');
Route::get('/sales', [SalesController::class, 'index'])->name('page.sales');
Route::get('/sales/{slug}', [SalesController::class, 'show'])->name('page.sales.article');

Route::domain('fr.gbsale.ru')->group(function () {
    Route::get('/', [FranchiseController::class, 'index'])->name('franchise.index');
    Route::post('/submit', [FranchiseController::class, 'submit'])->name('franchise.submit');
});

Route::get('/franchise', [FranchiseController::class, 'index'])->name('franchise.index.fallback');
Route::post('/franchise/submit', [FranchiseController::class, 'submit'])->name('franchise.submit.fallback');
