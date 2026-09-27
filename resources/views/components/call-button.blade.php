@props(['variant' => 'success', 'size' => null, 'location' => 'body', 'label' => null])
<a href="tel:{{ config('site.phone.e164') }}" {{ $attributes->class(['btn', 'btn-'.$variant, 'btn-'.$size => $size]) }} data-track="click_call" data-track-location="{{ $location }}">
    <x-icon.phone />
    <span>@if ($label){{ $label }} @endif<span class="ltr">{{ config('site.phone.display') }}</span></span>
</a>
