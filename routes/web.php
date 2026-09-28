<?php

use App\Http\Controllers\AreaController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CspReportController;
use App\Http\Controllers\IndexNowKeyController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PriceGuideController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\RobotsController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\ThankYouController;
use Illuminate\Support\Facades\Route;
use Spatie\Honeypot\ProtectAgainstSpam;

Route::middleware('page-cache')->group(function () {
    Route::get('/', [PageController::class, 'home'])->name('home');
    Route::get('/about', [PageController::class, 'about'])->name('about');
    Route::get('/contact', [PageController::class, 'contact'])->name('contact');

    foreach (array_keys(PageController::POLICIES) as $policy) {
        Route::get('/'.$policy, [PageController::class, 'policy'])->defaults('slug', $policy)->name($policy);
    }

    Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
    Route::get('/services/{slug}', [ServiceController::class, 'show'])->where('slug', '[a-z0-9-]+')->name('services.show');

    Route::get('/areas', [AreaController::class, 'index'])->name('areas.index');
    Route::get('/areas/{slug}', [AreaController::class, 'show'])->where('slug', '[a-z0-9-]+')->name('areas.show');

    Route::get('/prices', [PriceGuideController::class, 'index'])->name('prices.index');
    Route::get('/prices/{slug}', [PriceGuideController::class, 'show'])->where('slug', '[a-z0-9-]+')->name('prices.show');

    // Curated facets are registered before the product catch-all (PLAN.md §4).
    Route::get('/store', [StoreController::class, 'index'])->name('store.index');
    Route::get('/store/brand/{brand}/{hp}', [StoreController::class, 'brandCapacity'])->where(['brand' => '[a-z0-9-]+', 'hp' => '[0-9]+(-[0-9]+)?-hp'])->name('store.brand-capacity');
    Route::get('/store/brand/{brand}', [StoreController::class, 'brand'])->where('brand', '[a-z0-9-]+')->name('store.brand');
    Route::get('/store/capacity/{hp}', [StoreController::class, 'capacity'])->where('hp', '[0-9]+(-[0-9]+)?-hp')->name('store.capacity');
    Route::get('/store/type/{type}', [StoreController::class, 'type'])->where('type', '[a-z-]+')->name('store.type');
    Route::get('/store/{slug}', [StoreController::class, 'product'])->where('slug', '[a-z0-9-]+')->name('store.product');

    Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
    Route::get('/blog/category/{slug}', [BlogController::class, 'category'])->where('slug', '[a-z0-9-]+')->name('blog.category');
    Route::get('/blog/author/{slug}', [BlogController::class, 'author'])->where('slug', '[a-z0-9-]+')->name('blog.author');
    Route::get('/blog/{slug}', [BlogController::class, 'show'])->where('slug', '[a-z0-9-]+')->name('blog.show');

    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('/projects/{slug}', [ProjectController::class, 'show'])->where('slug', '[a-z0-9-]+')->name('projects.show');

    Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');
});

// Personal, never cached, noindex (and disallowed in robots.txt).
Route::middleware('no-page-cache')->group(function () {
    Route::get('/cart', [CartController::class, 'show'])->name('cart');
    Route::post('/cart/items', [CartController::class, 'add'])->middleware('throttle:30,1')->name('cart.add');
    Route::patch('/cart/items/{product}', [CartController::class, 'update'])->whereNumber('product')->name('cart.update');
    Route::delete('/cart/items/{product}', [CartController::class, 'remove'])->whereNumber('product')->name('cart.remove');
    Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout');
    Route::post('/checkout', [CheckoutController::class, 'store'])->middleware('throttle:5,1')->name('checkout.store');
    Route::get('/search', SearchController::class)->name('search');
});

Route::post('/bookings', [BookingController::class, 'store'])
    ->middleware(['throttle:5,1', ProtectAgainstSpam::class])
    ->name('bookings.store');

Route::post('/reviews', [ReviewController::class, 'store'])
    ->middleware(['throttle:3,1', ProtectAgainstSpam::class])
    ->name('reviews.store');

Route::get('/thank-you', ThankYouController::class)->name('thank-you');

Route::get('/robots.txt', RobotsController::class)->name('robots');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap.index');
Route::get('/sitemaps/{name}.xml', [SitemapController::class, 'child'])
    ->where('name', '[a-z0-9-]+')
    ->name('sitemap.child');

Route::get('/{key}.txt', IndexNowKeyController::class)->where('key', '[a-z0-9-]{8,128}')->name('indexnow.key');

Route::post('/csp-report', CspReportController::class)
    ->middleware('throttle:30,1')
    ->name('csp-report');
