@php($nav = app(\App\Support\Navigation::class)->main())
<header class="site-header">
    <x-louver variant="thin" />
    <div class="container site-header__bar">
        <a class="brand" href="{{ route('home') }}" @if (request()->routeIs('home')) aria-current="page" @endif>
            <x-site.logo class="brand__logo" width="44" height="44" />
            <span class="brand__name">{{ config('site.brand.name') }}</span>
        </a>

        <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-nav" data-nav-toggle>القائمة</button>

        <nav id="site-nav" class="site-nav" aria-label="القائمة الرئيسية">
            <ul>
                @foreach ($nav as $item)
                    <li><a href="{{ $item['url'] }}" @if (request()->routeIs($item['route'])) aria-current="page" @endif>{{ $item['label'] }}</a></li>
                @endforeach
            </ul>
        </nav>

        <x-call-button class="site-header__call" location="header" label="اتصل" />
    </div>
</header>
