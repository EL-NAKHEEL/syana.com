<?php

use App\Http\Controllers\AreaController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CspReportController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PriceGuideController;
use App\Http\Controllers\RobotsController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\ThankYouController;
use Illuminate\Support\Facades\Route;
use Spatie\Honeypot\ProtectAgainstSpam;

Route::middleware('page-cache')->group(function () {
    Route::get('/', [PageController::class, 'home'])->name('home');
    Route::get('/about', [PageController::class, 'about'])->name('about');
    Route::get('/contact', [PageController::class, 'contact'])->name('contact');

    Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
    Route::get('/services/{slug}', [ServiceController::class, 'show'])->where('slug', '[a-z0-9-]+')->name('services.show');

    Route::get('/areas', [AreaController::class, 'index'])->name('areas.index');
    Route::get('/areas/{slug}', [AreaController::class, 'show'])->where('slug', '[a-z0-9-]+')->name('areas.show');

    Route::get('/prices', [PriceGuideController::class, 'index'])->name('prices.index');
    Route::get('/prices/{slug}', [PriceGuideController::class, 'show'])->where('slug', '[a-z0-9-]+')->name('prices.show');
});

Route::post('/bookings', [BookingController::class, 'store'])
    ->middleware(['throttle:5,1', ProtectAgainstSpam::class])
    ->name('bookings.store');

Route::get('/thank-you', ThankYouController::class)->name('thank-you');

Route::get('/robots.txt', RobotsController::class)->name('robots');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap.index');
Route::get('/sitemaps/{name}.xml', [SitemapController::class, 'child'])
    ->where('name', '[a-z0-9-]+')
    ->name('sitemap.child');

Route::post('/csp-report', CspReportController::class)
    ->middleware('throttle:30,1')
    ->name('csp-report');
