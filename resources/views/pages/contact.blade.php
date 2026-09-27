@php
    use App\Support\Copy;
    $business = app(\App\Settings\BusinessSettings::class);
    $email = $business->publicEmail();
@endphp
<x-layout class="page-contact">
    <header class="page-head">
        <div class="container">
            <h1>{{ $page->title }}</h1>
            @if ($page->intro)
                <p>{{ Copy::text($page->intro) }}</p>
            @endif
        </div>
    </header>

    <section class="section section--paper" aria-labelledby="contact-ways">
        <div class="container">
            <h2 id="contact-ways">طرق التواصل</h2>
            <ul class="nap">
                <li>
                    <span class="nap__label">اتصال</span>
                    <a class="nap__value" href="tel:{{ config('site.phone.e164') }}" data-track="click_call" data-track-location="contact"><span class="ltr">{{ config('site.phone.display') }}</span></a>
                </li>
                <li>
                    <span class="nap__label">واتساب</span>
                    <a class="nap__value" href="https://wa.me/{{ config('site.phone.whatsapp') }}" data-track="click_whatsapp" data-track-location="contact" rel="noopener"><span class="ltr">{{ config('site.phone.display') }}</span></a>
                </li>
                @if ($email)
                    <li>
                        <span class="nap__label">البريد الإلكتروني</span>
                        <a class="nap__value" href="mailto:{{ $email }}">{{ $email }}</a>
                    </li>
                @endif
                @if ($business->publicAddressIsKnown())
                    <li>
                        <span class="nap__label">العنوان</span>
                        <span class="nap__value">{{ $business->street_address }}، {{ $business->locality }}</span>
                    </li>
                @endif
            </ul>

            @if ($page->body)
                <div class="prose stack-top">{{ Copy::html($page->body) }}</div>
            @endif
        </div>
    </section>

    <x-faq :faqs="$page->faqs" class="section--paper" />
</x-layout>
