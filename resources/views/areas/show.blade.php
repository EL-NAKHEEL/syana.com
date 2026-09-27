@php use App\Support\Copy; @endphp
<x-layout class="page-area">
    <x-page-header :title="'صيانة وتركيب تكييفات في '.$area->name_ar" :intro="$area->response_time_note" />

    <section class="section" aria-labelledby="area-intro">
        <div class="container two-col">
            <div>
                <span class="eyebrow">{{ $area->name_ar }}</span>
                <h2 id="area-intro">النخيل كوول في {{ $area->name_ar }}</h2>
                <div class="prose">{!! nl2br((string) Copy::text($area->local_intro)) !!}</div>
                @if ($area->local_notes)
                    <h3>ملاحظات مهمة في {{ $area->name_ar }}</h3>
                    <p>{{ Copy::text($area->local_notes) }}</p>
                @endif
            </div>
            <x-picture name="technician-installing-outdoor-ac-unit" :alt="'فني تكييف من النخيل كوول في '.$area->name_ar" sizes="(min-width: 992px) 600px, 100vw" />
        </div>
    </section>

    @if ($services->isNotEmpty())
        <section class="section section--light" aria-labelledby="area-services">
            <div class="container">
                <div class="section-title--center">
                    <span class="eyebrow">خدماتنا</span>
                    <h2 id="area-services">الخدمات المتاحة في {{ $area->name_ar }}</h2>
                </div>
                <ul class="service-grid">
                    @foreach ($services as $service)
                        <li class="service-card">
                            @if ($service->image)
                                <x-picture class="service-card__img" :name="$service->image" :alt="$service->name" sizes="106px" />
                            @endif
                            <h3><a href="{{ $service->url() }}">{{ $service->name }}</a></h3>
                            <p>{{ Copy::text($service->pivot->note ?: $service->summary) }}</p>
                            <a class="btn" href="{{ $service->url() }}">التفاصيل</a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    <x-faq :faqs="$area->faqs" :heading="'أسئلة عن الخدمة في '.$area->name_ar" />

    @if ($neighbors->isNotEmpty())
        <section class="section" aria-labelledby="area-neighbors">
            <div class="container">
                <h2 id="area-neighbors">مناطق قريبة من {{ $area->name_ar }}</h2>
                <ul class="link-list">
                    @foreach ($neighbors as $neighbor)
                        <li><a href="{{ $neighbor->url() }}">صيانة تكييفات في {{ $neighbor->name_ar }}</a></li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    <x-booking-form :area="$area" :heading="'احجز معاينة في '.$area->name_ar" />
</x-layout>
