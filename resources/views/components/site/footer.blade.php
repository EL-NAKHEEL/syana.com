@php($navigation = app(\App\Support\Navigation::class))
@php($email = app(\App\Settings\BusinessSettings::class)->publicEmail())
<footer class="site-footer on-dark">
    <x-louver variant="reverse" />
    <div class="container site-footer__grid">
        <section aria-labelledby="footer-brand">
            <h2 id="footer-brand">{{ config('site.brand.name') }}</h2>
            <p>بيع وتركيب وصيانة وتأسيس التكييفات للبيوت والشركات.</p>
            <x-call-button variant="primary" location="footer" />
        </section>

        <section aria-labelledby="footer-links">
            <h2 id="footer-links">روابط</h2>
            <ul>
                @foreach ($navigation->footer() as $item)
                    <li><a href="{{ $item['url'] }}">{{ $item['label'] }}</a></li>
                @endforeach
            </ul>
        </section>

        <section aria-labelledby="footer-contact">
            <h2 id="footer-contact">تواصل</h2>
            <ul>
                <li><a href="tel:{{ config('site.phone.e164') }}" data-track="click_call" data-track-location="footer">تليفون: <span class="ltr">{{ config('site.phone.display') }}</span></a></li>
                <li><a href="https://wa.me/{{ config('site.phone.whatsapp') }}" data-track="click_whatsapp" data-track-location="footer" rel="noopener">واتساب: <span class="ltr">{{ config('site.phone.display') }}</span></a></li>
                @if ($email)
                    <li><a href="mailto:{{ $email }}">{{ $email }}</a></li>
                @endif
                @if ($review = $navigation->reviewUrl())
                    <li><a href="{{ $review }}" rel="noopener">قيّمنا على جوجل</a></li>
                @endif
            </ul>
        </section>
    </div>
    <div class="container site-footer__legal">
        <p>© {{ now()->year }} {{ config('site.brand.name') }}. جميع الحقوق محفوظة.</p>
    </div>
</footer>
