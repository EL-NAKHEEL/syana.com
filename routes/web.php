<?php

use App\Http\Controllers\CspReportController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\RobotsController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::middleware('page-cache')->group(function () {
    Route::get('/', [PageController::class, 'home'])->name('home');
    Route::get('/about', [PageController::class, 'about'])->name('about');
    Route::get('/contact', [PageController::class, 'contact'])->name('contact');
});

Route::get('/robots.txt', RobotsController::class)->name('robots');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap.index');
Route::get('/sitemaps/{name}.xml', [SitemapController::class, 'child'])
    ->where('name', '[a-z0-9-]+')
    ->name('sitemap.child');

Route::post('/csp-report', CspReportController::class)
    ->middleware('throttle:30,1')
    ->name('csp-report');
