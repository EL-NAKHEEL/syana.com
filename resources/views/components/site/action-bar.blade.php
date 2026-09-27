{{-- Sticky mobile bar: call / WhatsApp / cart (cart appears once the store exists). --}}
<nav class="action-bar" aria-label="تواصل سريع">
    <a class="action-bar__call" href="tel:{{ config('site.phone.e164') }}" data-track="click_call" data-track-location="action_bar"><x-icon.phone /> اتصل</a>
    <a class="action-bar__whatsapp" href="https://wa.me/{{ config('site.phone.whatsapp') }}" data-track="click_whatsapp" data-track-location="action_bar" rel="noopener"><x-icon.whatsapp /> واتساب</a>
    @if (Route::has('cart'))
        <a class="action-bar__cart" href="{{ route('cart') }}"><x-icon.cart /> السلة</a>
    @endif
</nav>
