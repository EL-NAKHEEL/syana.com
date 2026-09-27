@php
    use App\Support\Copy;
    $business = app(\App\Settings\BusinessSettings::class);
    $email = $business->publicEmail();
@endphp
<x-layout class="page-contact">
    <x-page-header :title="$page->title" :intro="$page->intro" />

    <section class="section" aria-labelledby="contact-ways">
        <div class="container">
            <div class="section-title--center">
                <span class="eyebrow">تواصل معنا</span>
                <h2 id="contact-ways">طرق التواصل</h2>
            </div>
            <ul class="nap">
                <li>
                    <span class="icon-circle"><x-icon.phone /></span>
                    <span class="nap__label">رقم الهاتف</span>
                    <span class="nap__value ltr">{{ config('site.phone.display') }}</span>
                    <x-call-button location="contact" label="اتصل الآن" />
                </li>
                <li>
                    <span class="icon-circle"><x-icon.whatsapp /></span>
                    <span class="nap__label">عن طريق الواتساب</span>
                    <span class="nap__value ltr">{{ config('site.phone.display') }}</span>
                    <x-whatsapp-button location="contact" label="اضغط للتواصل" />
                </li>
                @if ($email)
                    <li>
                        <span class="icon-circle"><x-icon.mail /></span>
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

    <x-booking-form />

    <x-faq :faqs="$page->faqs" />
</x-layout>
