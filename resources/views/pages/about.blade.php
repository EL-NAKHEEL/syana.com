@php use App\Support\Copy; @endphp
<x-layout class="page-about">
    <header class="page-head">
        <div class="container">
            <h1>{{ $page->title }}</h1>
            @if ($page->intro)
                <p>{{ Copy::text($page->intro) }}</p>
            @endif
        </div>
    </header>

    @if ($page->body)
        <section class="section section--paper">
            <div class="container prose">{{ Copy::html($page->body) }}</div>
        </section>
    @endif

    <x-faq :faqs="$page->faqs" class="section--paper" />

    <div class="container">
        <x-cta-band />
    </div>
</x-layout>
