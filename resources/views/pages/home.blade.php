@php
    use App\Support\Copy;
    /** @var \App\Models\Page $page */
    $hero = $page->data('hero', []);
    $years = ($founded = app(\App\Settings\BusinessSettings::class)->founding_year) ? now()->year - $founded : null;
@endphp
<x-layout class="page-home">
    {{-- Hero: first slide of the existing site's carousel, kept static (one H1, fast LCP). --}}
    <section class="hero" aria-labelledby="hero-title">
        <x-picture name="split-ac-units-hot-and-cool-air" alt="وحدات تكييف سبليت بتطلع هوا ساقع وسخن" sizes="100vw" :eager="true" />
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

    @if ($about = $page->data('about'))
        <section class="section" aria-labelledby="about-title">
            <div class="container two-col">
                <div class="about-photos">
                    <x-picture name="technician-servicing-indoor-split-ac" alt="فني بيصين وحدة تكييف داخلية" sizes="(min-width: 992px) 300px, 45vw" />
                    <x-picture name="technician-installing-outdoor-ac-unit" alt="فني بيركّب وحدة تكييف خارجية" sizes="(min-width: 992px) 300px, 45vw" />
                </div>
                <div>
                    <span class="eyebrow">{{ $about['eyebrow'] ?? 'من نحن' }}</span>
                    <h2 id="about-title">{{ $about['heading'] }}</h2>
                    <p>{{ Copy::text($about['text'] ?? '') }}</p>
                    <div class="experience">
                        @if ($years)
                            <div class="experience__box">
                                <span class="experience__number">{{ $years }}</span>
                                <span class="experience__label">سنة من الخبرة</span>
                            </div>
                        @endif
                        <ul class="check-list">
                            @foreach ($about['checklist'] ?? [] as $item)
                                <li>{{ Copy::text($item) }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <ul class="contact-items">
                        @if ($email = app(\App\Settings\BusinessSettings::class)->publicEmail())
                            <li class="contact-item">
                                <span class="icon-circle"><x-icon.mail /></span>
                                <div><p>راسلنا عبر البريد</p><a href="mailto:{{ $email }}">{{ $email }}</a></div>
                            </li>
                        @endif
                        <li class="contact-item">
                            <span class="icon-circle"><x-icon.phone /></span>
                            <div><p>اتصل بنا</p><a href="tel:{{ config('site.phone.e164') }}" data-track="click_call" data-track-location="about"><span class="ltr">{{ config('site.phone.display') }}</span></a></div>
                        </li>
                    </ul>
                </div>
            </div>
        </section>
    @endif

    @if ($why = $page->data('why'))
        <section class="section" aria-labelledby="why-title">
            <div class="container two-col">
                <x-picture name="technician-on-ladder-servicing-ac" alt="فني على سلم بيصين تكييف على واجهة مبنى" sizes="(min-width: 992px) 600px, 100vw" />
                <div>
                    <span class="eyebrow">ميزاتنا</span>
                    <h2 id="why-title">{{ $why['heading'] }}</h2>
                    <ul class="features">
                        @foreach ($why['items'] ?? [] as $item)
                            <li class="feature">
                                <span class="icon-circle"><x-icon.check /></span>
                                <div>
                                    <h3>{{ $item['title'] }}</h3>
                                    <p>{{ Copy::text($item['text']) }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                    <x-call-button size="lg" location="why" label="اضغط للاتصال" />
                </div>
            </div>
        </section>
    @endif

    @if ($services = $page->data('services'))
        <section class="section" aria-labelledby="services-title">
            <div class="container">
                <div class="section-title--center">
                    <span class="eyebrow">خدماتنا</span>
                    <h2 id="services-title">{{ $services['heading'] }}</h2>
                    @if (! empty($services['intro']))
                        <p>{{ Copy::text($services['intro']) }}</p>
                    @endif
                </div>
                <ul class="service-grid">
                    @foreach ($services['items'] ?? [] as $item)
                        <li class="service-card">
                            @if (! empty($item['image']))
                                <x-picture class="service-card__img" :name="$item['image']" :alt="$item['title']" sizes="106px" />
                            @endif
                            <h3>{{ $item['title'] }}</h3>
                            <p>{{ Copy::text($item['text']) }}</p>
                            <a class="btn" href="tel:{{ config('site.phone.e164') }}" data-track="click_call" data-track-location="service_card">اضغط للاتصال</a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    @if ($process = $page->data('process'))
        <section class="section section--dark" aria-labelledby="process-title">
            <div class="container">
                <div class="section-title--center">
                    <span class="eyebrow">خطوات الشغل</span>
                    <h2 id="process-title">{{ $process['heading'] }}</h2>
                </div>
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
        <section class="section">
            <div class="container prose">{{ Copy::html($page->body) }}</div>
        </section>
    @endif

    <x-faq :faqs="$page->faqs" />

    <section class="section">
        <div class="container">
            <x-contact-band :heading="$page->data('cta.heading', 'محتاج فني تكييف؟')" :text="$page->data('cta.text', 'كلّمنا أو ابعت واتساب، وهنحدد معاك معاد المعاينة.')" />
        </div>
    </section>
</x-layout>
