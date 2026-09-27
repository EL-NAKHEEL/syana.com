@php
    use App\Support\Copy;
    /** @var \App\Models\Page $page */
    $hero = $page->data('hero', []);
@endphp
<x-layout class="page-home">
    <section class="hero" aria-labelledby="hero-title">
        <div class="container hero__grid">
            <div>
                <h1 id="hero-title" class="hero__title">
                    <span class="hero__slogan">{{ $hero['slogan'] ?? 'خلّي الحرّ برّه.' }}</span>
                    <span class="hero__keywords">{{ $hero['keywords'] ?? 'بيع وتركيب وصيانة التكييفات في مصر' }}</span>
                </h1>
                @if (! empty($hero['lead']))
                    <p class="hero__lead">{{ Copy::text($hero['lead']) }}</p>
                @endif
                <div class="btn-row">
                    <x-call-button variant="ink" size="lg" location="hero" label="اتصل دلوقتي" />
                    <x-whatsapp-button variant="paper" size="lg" location="hero" />
                </div>
                @if (! empty($hero['stamp']))
                    <x-stamp>{{ $hero['stamp'] }}</x-stamp>
                @endif
            </div>

            <div class="ac-unit" aria-hidden="true">
                <div class="ac-unit__body">
                    <div class="ac-unit__top">
                        <span class="ac-unit__badge">{{ config('site.brand.name') }}</span>
                        <span class="lcd ac-unit__lcd"><span class="lcd__value temp-readout"></span></span>
                    </div>
                    <div class="ac-unit__vents"><span></span><span></span><span></span><span></span></div>
                </div>
                <div class="ac-unit__air"><span></span><span></span><span></span></div>
            </div>
        </div>
    </section>

    @if ($services = $page->data('services'))
        <section class="section section--t38" aria-labelledby="services-title">
            <div class="container">
                <div class="section__head">
                    <div>
                        <span class="eyebrow">خدماتنا</span>
                        <h2 id="services-title">{{ $services['heading'] }}</h2>
                    </div>
                </div>
                @if (! empty($services['intro']))
                    <p class="prose">{{ Copy::text($services['intro']) }}</p>
                @endif
                <ul class="cards">
                    @foreach ($services['items'] ?? [] as $item)
                        <li class="card">
                            <h3>{{ $item['title'] }}</h3>
                            <p>{{ Copy::text($item['text']) }}</p>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    @if ($why = $page->data('why'))
        <section class="section section--t30" aria-labelledby="why-title">
            <div class="container">
                <span class="eyebrow">مميزاتنا</span>
                <h2 id="why-title">{{ $why['heading'] }}</h2>
                <ul class="cards">
                    @foreach ($why['items'] ?? [] as $item)
                        <li class="card">
                            <h3>{{ $item['title'] }}</h3>
                            <p>{{ Copy::text($item['text']) }}</p>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    @if ($process = $page->data('process'))
        <section class="section section--t24" aria-labelledby="process-title">
            <div class="container">
                <span class="eyebrow">خطوات الشغل</span>
                <h2 id="process-title">{{ $process['heading'] }}</h2>
                <ol class="steps">
                    @foreach ($process['steps'] ?? [] as $step)
                        <li>
                            <h3>{{ $step['title'] }}</h3>
                            <p>{{ Copy::text($step['text']) }}</p>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>
    @endif

    @if ($page->body)
        <section class="section section--paper">
            <div class="container prose">{{ Copy::html($page->body) }}</div>
        </section>
    @endif

    <x-faq :faqs="$page->faqs" class="section--paper" />

    <section class="section section--paper">
        <div class="container">
            <x-cta-band :heading="$page->data('cta.heading', 'محتاج فني تكييف؟')" :text="$page->data('cta.text', 'كلّمنا أو ابعت واتساب، وهنحدد معاك معاد المعاينة.')" />
        </div>
    </section>

    <span class="lcd thermo" aria-hidden="true"><span class="lcd__value temp-readout"></span><span class="lcd__label">الحرارة</span></span>
</x-layout>
