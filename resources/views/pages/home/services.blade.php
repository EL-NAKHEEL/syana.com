@php use App\Support\Copy; @endphp
@if ($services = $page->data('services'))
    <section class="section" aria-labelledby="services-title">
        <div class="container">
            <div class="section-title--center">
                <span class="eyebrow">{{ ($services['eyebrow'] ?? '') ?: 'خدماتنا' }}</span>
                <h2 id="services-title">{{ $services['heading'] }}</h2>
                @if (! empty($services['intro']))
                    <p>{{ Copy::text($services['intro']) }}</p>
                @endif
            </div>
            @php($liveServices = app(\App\Support\Navigation::class)->services()->keyBy('slug'))
            <ul class="service-grid">
                @foreach ($services['items'] ?? [] as $item)
                    @php($linked = $liveServices->get($item['service'] ?? ''))
                    <li class="service-card">
                        @if (! empty($item['image']) || $linked?->getFirstMedia('image'))
                            <x-image class="service-card__img" :media="$linked?->getFirstMedia('image')" :fallback="$item['image'] ?? null" :alt="$item['title']" sizes="106px" />
                        @endif
                        <h3>@if ($linked)<a href="{{ $linked->url() }}">{{ $item['title'] }}</a>@else{{ $item['title'] }}@endif</h3>
                        <p>{{ Copy::text($item['text']) }}</p>
                        @if ($linked)
                            <a class="btn" href="{{ $linked->url() }}">التفاصيل</a>
                        @else
                            <a class="btn" href="tel:{{ config('site.phone.e164') }}" data-track="click_call" data-track-location="service_card">اضغط للاتصال</a>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    </section>
@endif
