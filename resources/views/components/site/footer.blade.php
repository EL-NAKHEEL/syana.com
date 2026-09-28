@php
    $navigation = app(\App\Support\Navigation::class);
    $business = app(\App\Settings\BusinessSettings::class);
    $email = $business->publicEmail();
@endphp
<footer class="footer">
    <div class="container footer__grid">
        <section aria-labelledby="footer-contact">
            <h2 id="footer-contact">تواصل معنا</h2>
            <p><a href="tel:{{ config('site.phone.e164') }}" data-track="click_call" data-track-location="footer">تليفون: <span class="ltr">{{ config('site.phone.display') }}</span></a></p>
            <p><a href="https://wa.me/{{ config('site.phone.whatsapp') }}" data-track="click_whatsapp" data-track-location="footer" rel="noopener">واتساب: <span class="ltr">{{ config('site.phone.display') }}</span></a></p>
            @if ($email)
                <p><a href="mailto:{{ $email }}">{{ $email }}</a></p>
            @endif
            @if ($review = $navigation->reviewUrl())
                <p><a href="{{ $review }}" rel="noopener">قيّمنا على جوجل</a></p>
            @endif
        </section>

        <section aria-labelledby="footer-links">
            <h2 id="footer-links">روابط سريعة</h2>
            <ul class="footer__links">
                @foreach ($navigation->footer() as $item)
                    <li><a href="{{ $item['url'] }}">{{ $item['label'] }}</a></li>
                @endforeach
            </ul>
        </section>

        @if (($footerAreas = $navigation->footerAreas())->isNotEmpty())
            <section aria-labelledby="footer-areas">
                <h2 id="footer-areas">مناطق الخدمة</h2>
                <ul class="footer__links">
                    @foreach ($footerAreas as $area)
                        <li><a href="{{ $area->url() }}">صيانة تكييفات {{ $area->name_ar }}</a></li>
                    @endforeach
                </ul>
            </section>
        @else
            <section aria-labelledby="footer-about">
                <h2 id="footer-about">{{ config('site.brand.name') }}</h2>
                <p>بيع وتركيب وصيانة وتأسيس التكييفات للبيوت والشركات.</p>
            </section>
        @endif
    </div>
    <div class="footer__copyright">
        <div class="container">
            <p>حقوق النشر © {{ now()->year }} {{ config('site.brand.name') }}، جميع الحقوق محفوظة.</p>
            @if (($policies = $navigation->policies()) !== [])
                <ul class="footer__policies">
                    @foreach ($policies as $item)
                        <li><a href="{{ $item['url'] }}">{{ $item['label'] }}</a></li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</footer>

{{-- Floating call / WhatsApp buttons, as on the existing site. --}}
<a class="float-btn float-btn--whatsapp" href="https://wa.me/{{ config('site.phone.whatsapp') }}" aria-label="راسلنا على واتساب" data-track="click_whatsapp" data-track-location="float" rel="noopener"><x-icon.whatsapp /></a>
<a class="float-btn float-btn--call" href="tel:{{ config('site.phone.e164') }}" aria-label="اتصل بنا {{ config('site.phone.display') }}" data-track="click_call" data-track-location="float"><x-icon.phone /></a>
