@php
    use App\Support\Copy;
    /** @var \App\Models\Page $page */
    $hero = $page->data('hero', []);
    $years = ($founded = app(\App\Settings\BusinessSettings::class)->founding_year) ? now()->year - $founded : null;
@endphp
<x-layout class="page-home">
    {{-- Hero: first slide of the existing site's carousel, kept static (one H1, fast LCP). --}}
    <section class="hero" aria-labelledby="hero-title">
        <x-image :media="$page->getFirstMedia('hero')" fallback="split-ac-units-hot-and-cool-air" alt="وحدات تكييف سبليت بتطلع هوا ساقع وسخن" sizes="100vw" :eager="true" />
        <div class="hero__caption">
            <div class="container">
                <div class="hero__inner">
                    @if (! empty($hero['label']))
                        <p class="hero__label">{{ $hero['label'] }}</p>
                    @endif
                    <h1 id="hero-title">
                        <span class="hero__slogan">{{ $hero['slogan'] ?? 'خلّي الحرّ برّه.' }}</span>
                        <span class="hero__keywords">{{ $hero['keywords'] ?? 'بيع وتركيب وصيانة التكييفات في مصر' }}</span>
                    </h1>
                    @if (! empty($hero['lead']))
                        <p class="hero__lead">{{ Copy::text($hero['lead']) }}</p>
                    @endif
                    <div class="btn-row">
                        <x-call-button size="lg" location="hero" />
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Sections in the owner's order; a section left out of the list is hidden (admin › الصفحات › الرئيسية). --}}
    @foreach ($page->homeSections() as $section)
        @include('pages.home.'.$section)
    @endforeach
</x-layout>
