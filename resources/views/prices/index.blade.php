@php use App\Support\Copy; @endphp
<x-layout class="page-prices">
    <x-hub-header :hub="$hub" title="الأسعار" intro="أسعار خدمات التكييف من جدول أسعارنا الحالي، وإيه اللي بيأثر على التكلفة." />

    <section class="section" aria-labelledby="guides-list">
        <div class="container">
            <h2 id="guides-list" class="visually-hidden">أدلة الأسعار</h2>
            <ul class="area-grid">
                @foreach ($guides as $guide)
                    <li class="area-card">
                        <h3><a href="{{ $guide->url() }}">{{ $guide->render($guide->h1) }}</a></h3>
                        @if ($guide->intro)
                            <p>{{ Copy::text(\Illuminate\Support\Str::limit($guide->intro, 140)) }}</p>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    </section>
    <x-hub-extra :hub="$hub" />
</x-layout>
