@php
    /** @var \App\Seo\Seo $seo */
    $seo = app(\App\Seo\Seo::class);
    $seoSettings = app(\App\Settings\SeoSettings::class);
    $analytics = app(\App\Settings\AnalyticsSettings::class);
    $canonical = $seo->canonicalUrl();
    $ogImage = $seo->ogImageUrl();
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl" class="no-js">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $seo->fullTitle() }}</title>
    <meta name="description" content="{{ $seo->metaDescription() }}">
    <meta name="robots" content="{{ $seo->robotsContent() }}">
    @if ($canonical)
        <link rel="canonical" href="{{ $canonical }}">
    @endif
    <link rel="preload" href="{{ Vite::asset('resources/fonts/web/changa-800.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="{{ Vite::asset('resources/fonts/web/plex-arabic-400.woff2') }}" as="font" type="font/woff2" crossorigin>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>{!! \App\Support\InlineScripts::HEAD !!}</script>

    <meta property="og:locale" content="ar_EG">
    <meta property="og:site_name" content="{{ config('site.brand.name') }}">
    <meta property="og:type" content="{{ $seo->openGraphType() }}">
    <meta property="og:title" content="{{ $seo->fullTitle() }}">
    <meta property="og:description" content="{{ $seo->metaDescription() }}">
    <meta property="og:url" content="{{ $seo->pageUrl() }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="{{ $seo->ogImageAlt() }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seo->fullTitle() }}">
    <meta name="twitter:description" content="{{ $seo->metaDescription() }}">
    <meta name="twitter:image" content="{{ $ogImage }}">

    @if ($seoSettings->google_site_verification)
        <meta name="google-site-verification" content="{{ $seoSettings->google_site_verification }}">
    @endif
    @if ($seoSettings->bing_site_verification)
        <meta name="msvalidate.01" content="{{ $seoSettings->bing_site_verification }}">
    @endif
    @if ($analytics->gtm_container_id)
        <meta name="nk-gtm" content="{{ $analytics->gtm_container_id }}">
    @elseif ($analytics->ga4_measurement_id)
        <meta name="nk-ga4" content="{{ $analytics->ga4_measurement_id }}">
    @endif

    <meta name="theme-color" content="#E73F1E">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="32x32">
    <link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}">

    <script type="application/ld+json">{!! $seo->jsonLd() !!}</script>
</head>
<body {{ $attributes }}>
    <a class="skip-link" href="#main">تخطَّ إلى المحتوى</a>
    @if ($seo->isPreviewing())
        <div class="draft-banner" role="status">مسودة غير منشورة — معاينة للإدارة فقط</div>
    @endif
    <x-site.header />
    <x-breadcrumbs :items="$seo->breadcrumbItems()" />
    <main id="main">
        {{ $slot }}
    </main>
    <x-site.footer />
    <x-site.action-bar />
</body>
</html>
