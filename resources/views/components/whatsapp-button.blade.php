@props(['variant' => 'primary', 'size' => null, 'location' => 'body', 'label' => 'كلمنا واتساب', 'message' => 'السلام عليكم، محتاج استفسار عن التكييف'])
<a href="https://wa.me/{{ config('site.phone.whatsapp') }}?text={{ rawurlencode($message) }}" {{ $attributes->class(['btn', 'btn-'.$variant, 'btn-'.$size => $size]) }} data-track="click_whatsapp" data-track-location="{{ $location }}" rel="noopener">
    <x-icon.whatsapp />
    <span>{{ $label }}</span>
</a>
