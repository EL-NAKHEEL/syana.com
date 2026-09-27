@php use App\Support\Copy; @endphp
<x-layout class="page-service">
    <x-page-header :title="$service->h1" :intro="$service->summary" />

    <section class="section" aria-labelledby="service-about">
        <div class="container two-col">
            <div>
                <span class="eyebrow">{{ $service->name }}</span>
                <h2 id="service-about">الخدمة بتشمل إيه؟</h2>
                @if ($service->intro)
                    <p>{{ Copy::text($service->intro) }}</p>
                @endif
                @if ($service->included)
                    <ul class="check-list">
                        @foreach ($service->included as $item)
                            <li>{{ Copy::text($item) }}</li>
                        @endforeach
                    </ul>
                @endif
                <div class="btn-row">
                    <a class="btn btn-primary" href="#booking">احجز معاينة</a>
                    <x-call-button location="service" />
                </div>
            </div>
            <x-picture name="technician-servicing-indoor-split-ac" :alt="'فني من النخيل كوول: '.$service->name" sizes="(min-width: 992px) 600px, 100vw" />
        </div>
    </section>

    @if ($service->warning_signs)
        <section class="section section--light" aria-labelledby="service-signs">
            <div class="container">
                <div class="section-title--center">
                    <span class="eyebrow">امتى تكلّمنا؟</span>
                    <h2 id="service-signs">علامات إنك محتاج {{ $service->name }}</h2>
                </div>
                <ul class="features">
                    @foreach ($service->warning_signs as $sign)
                        <li class="feature">
                            <span class="icon-circle"><x-icon.check /></span>
                            <p>{{ Copy::text($sign) }}</p>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    @if ($service->process_steps)
        <section class="section section--dark" aria-labelledby="service-process">
            <div class="container">
                <div class="section-title--center">
                    <span class="eyebrow">خطوات الشغل</span>
                    <h2 id="service-process">بنشتغل إزاي؟</h2>
                </div>
                <ol class="steps">
                    @foreach ($service->process_steps as $step)
                        <li>
                            <h3>{{ $step['title'] }}</h3>
                            <p>{{ Copy::text($step['text']) }}</p>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>
    @endif

    <section class="section" aria-labelledby="service-price">
        <div class="container two-col price-block">
            <div>
                <span class="eyebrow">التكلفة</span>
                <h2 id="service-price">إيه اللي بيحدد السعر؟</h2>
                @if ($service->price_factors)
                    <ul class="check-list">
                        @foreach ($service->price_factors as $factor)
                            <li>{{ Copy::text($factor) }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
            <div class="price-box">
                @if ($service->starting_price !== null)
                    <p class="price-box__label">يبدأ من</p>
                    <p class="price-box__value">{{ number_format((float) $service->starting_price) }} <span>جنيه</span></p>
                @else
                    <p class="price-box__value price-box__value--note">{{ $service->price_note ?: 'السعر بعد المعاينة' }}</p>
                @endif
                <p>الفني بيقولك التكلفة بالظبط بعد المعاينة وقبل أي شغل.</p>
                <x-call-button location="service_price" label="اسأل عن السعر" />
            </div>
        </div>
    </section>

    @if ($service->body)
        <section class="section">
            <div class="container prose">{{ Copy::html($service->body) }}</div>
        </section>
    @endif

    <x-faq :faqs="$service->faqs" />

    @if ($areas->isNotEmpty())
        <section class="section" aria-labelledby="service-areas">
            <div class="container">
                <h2 id="service-areas">{{ $service->name }} في مناطقنا</h2>
                <ul class="link-list">
                    @foreach ($areas as $area)
                        <li><a href="{{ $area->url() }}">{{ $service->name }} في {{ $area->name_ar }}</a></li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    <x-booking-form :service="$service" :heading="'احجز معاينة: '.$service->name" />

    @if ($others->isNotEmpty())
        <section class="section" aria-labelledby="other-services">
            <div class="container">
                <div class="section-title--center">
                    <span class="eyebrow">خدماتنا</span>
                    <h2 id="other-services">خدمات تانية ممكن تحتاجها</h2>
                </div>
                <ul class="service-grid">
                    @foreach ($others->take(3) as $other)
                        <li class="service-card">
                            @if ($other->image)
                                <x-picture class="service-card__img" :name="$other->image" :alt="$other->name" sizes="106px" />
                            @endif
                            <h3><a href="{{ $other->url() }}">{{ $other->name }}</a></h3>
                            <p>{{ Copy::text($other->summary) }}</p>
                            <a class="btn" href="{{ $other->url() }}">التفاصيل</a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif
</x-layout>
