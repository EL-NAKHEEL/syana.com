{{--
    Responsive image: AVIF → WebP → JPEG, explicit width/height (no CLS), lazy by default.
    Pass :eager="true" for the LCP image (adds fetchpriority="high"). Arabic alt text is required.
--}}
@props(['name', 'alt', 'sizes' => '100vw', 'eager' => false, 'imgClass' => null])
@php
    $image = \App\Support\Placeholders::get($name);
    $width = max($image['widths']);
    $height = (int) round($width * $image['ratio']);
    $fallback = asset("images/placeholders/{$name}-".min($image['widths']).'.jpg');
@endphp
<picture {{ $attributes }}>
    <source type="image/avif" srcset="{{ \App\Support\Placeholders::srcset($name, 'avif') }}" sizes="{{ $sizes }}">
    <source type="image/webp" srcset="{{ \App\Support\Placeholders::srcset($name, 'webp') }}" sizes="{{ $sizes }}">
    <img src="{{ $fallback }}" srcset="{{ \App\Support\Placeholders::srcset($name, 'jpg') }}" sizes="{{ $sizes }}"
         alt="{{ $alt }}" width="{{ $width }}" height="{{ $height }}" @class([$imgClass])
         @if ($eager) loading="eager" fetchpriority="high" @else loading="lazy" @endif decoding="async">
</picture>
