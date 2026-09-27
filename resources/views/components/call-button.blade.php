@props(['variant' => 'primary', 'size' => null, 'location' => 'body', 'label' => 'اتصل بينا'])
<a href="tel:{{ config('site.phone.e164') }}" {{ $attributes->class(['btn', 'btn--'.$variant, 'btn--'.$size => $size]) }} data-track="click_call" data-track-location="{{ $location }}">
    <x-icon.phone />
    <span>{{ $label }} <span class="ltr">{{ config('site.phone.display') }}</span></span>
</a>
