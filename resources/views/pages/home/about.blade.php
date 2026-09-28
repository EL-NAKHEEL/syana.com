@php use App\Support\Copy; @endphp
@if ($about = $page->data('about'))
    <section class="section" aria-labelledby="about-title">
        <div class="container two-col">
            <div class="about-photos">
                @php($gallery = $page->getMedia('gallery'))
                <x-image :media="$gallery->get(0)" fallback="technician-servicing-indoor-split-ac" alt="فني بيصين وحدة تكييف داخلية" sizes="(min-width: 992px) 300px, 45vw" />
                <x-image :media="$gallery->get(1)" fallback="technician-installing-outdoor-ac-unit" alt="فني بيركّب وحدة تكييف خارجية" sizes="(min-width: 992px) 300px, 45vw" />
            </div>
            <div>
                <span class="eyebrow">{{ ($about['eyebrow'] ?? '') ?: 'من نحن' }}</span>
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
