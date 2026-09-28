@php
    $nav = app(\App\Support\Navigation::class);
    $social = app(\App\Settings\BusinessSettings::class)->same_as;
    $layout = app(\App\Settings\LayoutSettings::class);
@endphp
<div class="topbar">
    <div class="topbar__phone">
        <a href="tel:{{ config('site.phone.e164') }}" data-track="click_call" data-track-location="topbar">{{ $layout->topbar_label }} <span class="ltr">{{ config('site.phone.display') }}</span></a>
    </div>
    <div class="topbar__follow">
        @if (count($social))
            <span>تابعنا على:</span>
            @foreach ($social as $url)
                <a href="{{ $url }}" rel="noopener">{{ parse_url($url, PHP_URL_HOST) }}</a>
            @endforeach
        @endif
    </div>
</div>

<header class="navbar">
    <a class="navbar__brand" href="{{ route('home') }}" @if (request()->routeIs('home')) aria-current="page" @endif>
        <span>{{ config('site.brand.name') }}</span>
    </a>

    <button class="navbar__toggle" type="button" aria-expanded="false" aria-controls="site-nav" aria-label="القائمة" data-nav-toggle>
        <x-icon.menu />
    </button>

    <nav id="site-nav" class="navbar__nav" aria-label="القائمة الرئيسية">
        <ul>
            @foreach ($nav->main() as $item)
                <li><a href="{{ $item['url'] }}" @if (request()->routeIs($item['route'])) aria-current="page" @endif>{{ $item['label'] }}</a></li>
            @endforeach
        </ul>
        @if (Route::has('cart'))
            <a class="navbar__cart" href="{{ route('cart') }}" data-cart-link hidden><x-icon.cart /> السلة <span class="navbar__cart-count" data-cart-count>0</span></a>
        @endif
        @if ($layout->header_cta_visible)
            <x-whatsapp-button class="navbar__cta" variant="primary" location="navbar" :label="$layout->header_cta_label" :message="$layout->header_cta_message" />
        @endif
    </nav>
</header>
