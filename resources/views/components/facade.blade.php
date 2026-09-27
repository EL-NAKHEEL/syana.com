{{-- Click-to-load embed (Google Maps / YouTube). Loads nothing third-party until clicked. --}}
@props(['src', 'title', 'label' => 'اعرض', 'poster' => null])
<div {{ $attributes->class(['facade']) }} data-facade-src="{{ $src }}" data-facade-title="{{ $title }}">
    @if ($poster)
        <img src="{{ $poster }}" alt="" loading="lazy" decoding="async" width="1280" height="720">
    @endif
    <button type="button" class="btn btn--primary" data-facade-load>{{ $label }}: {{ $title }}</button>
</div>
