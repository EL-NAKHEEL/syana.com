<?php

use App\Seo\Sitemap\AreasSitemap;
use App\Seo\Sitemap\FacetsSitemap;
use App\Seo\Sitemap\PagesSitemap;
use App\Seo\Sitemap\PostsSitemap;
use App\Seo\Sitemap\PricesSitemap;
use App\Seo\Sitemap\ProductsSitemap;
use App\Seo\Sitemap\ProjectsSitemap;
use App\Seo\Sitemap\ServicesSitemap;

return [

    /*
    | Brand constants. Spelling must match the Google Business Profile exactly.
    */
    'brand' => [
        'name' => 'النخيل كوول',
        'alternate_names' => ['شركة النخيل', 'Al Nakheel Cool'],
        'title_separator' => ' | ',
    ],

    /*
    | The only public phone number (calls, WhatsApp and schema).
    */
    'phone' => [
        'display' => '01055207525',
        'e164' => '+201055207525',
        'whatsapp' => '201055207525',
    ],

    /*
    | Canonical host: https, non-www, lowercase, no trailing slash. The host and scheme
    | come from APP_URL; enforcement is switched off locally and in tests by default.
    */
    'force_canonical_host' => (bool) env('SITE_FORCE_CANONICAL_HOST', env('APP_ENV') === 'production'),

    'seo' => [
        'title_max' => 60,
        'title_hard_max' => 65,
        'description_min' => 120,
        'description_max' => 160,
        'description_hard_min' => 100,
        'description_hard_max' => 170,
        // Hubs (areas, projects, reviews, blog, categories) stay noindex below this count.
        'hub_min_items' => 3,
        // Facets are indexable only with at least this many live products.
        'facet_min_products' => 3,
        // Query parameters that make a URL a filtered/sorted variant (noindex, no canonical).
        'filter_params' => ['brand', 'hp', 'type', 'cooling', 'inverter', 'price_min', 'price_max', 'sort', 'rating', 'service', 'area', 'room', 'sun', 'top', 'q'],
        // Query parameters that never change content (canonical drops them).
        'tracking_params' => ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'utm_id', 'gclid', 'gbraid', 'wbraid', 'fbclid', 'msclkid', 'ttclid'],
    ],

    /*
    | Child sitemaps, in index order. Each provider lists only indexable, published, canonical URLs.
    */
    'sitemaps' => [
        PagesSitemap::class,
        ServicesSitemap::class,
        AreasSitemap::class,
        PricesSitemap::class,
        ProductsSitemap::class,
        FacetsSitemap::class,
        PostsSitemap::class,
        ProjectsSitemap::class,
    ],

    'security' => [
        'hsts' => (bool) env('SECURITY_HSTS', false),
        'csp_enforce' => (bool) env('SECURITY_CSP_ENFORCE', false),
        'csp_report_uri' => '/csp-report',
    ],

    'notifications' => [
        'email' => env('NOTIFY_EMAIL'),
    ],

    'indexnow' => [
        'enabled' => (bool) env('INDEXNOW_ENABLED', env('APP_ENV') === 'production'),
        'endpoint' => 'https://api.indexnow.org/indexnow',
    ],

];
