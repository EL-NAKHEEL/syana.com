{{--
    Owner-uploaded photo (WebP 800/1600 + original, explicit width/height) with a placeholder fallback.
    Arabic alt comes from the upload's «alt» field, else from the :alt prop. Pass :eager="true" for the LCP image.
--}}
@props(['media' => null, 'fallback' => null, 'alt' => '', 'sizes' => '100vw', 'eager' => false, 'imgClass' => null])
@if ($media)
    @php
        $width = (int) ($media->getCustomProperty('width') ?: 1600);
        $height = (int) ($media->getCustomProperty('height') ?: 900);
        $small = min(800, $width);
        $large = min(1600, $width);
        $altText = $alt === '' ? '' : ($media->getCustomProperty('alt') ?: $alt);
    @endphp
    <picture {{ $attributes }}>
        <source type="image/webp" srcset="{{ $media->getUrl('sm') }} {{ $small }}w, {{ $media->getUrl('lg') }} {{ $large }}w" sizes="{{ $sizes }}">
        <img src="{{ $media->getUrl() }}" alt="{{ $altText }}" width="{{ $width }}" height="{{ $height }}" @class([$imgClass])
             @if ($eager) loading="eager" fetchpriority="high" @else loading="lazy" @endif decoding="async">
    </picture>
@elseif ($fallback)
    <x-picture :name="$fallback" :alt="$alt" :sizes="$sizes" :eager="$eager" :img-class="$imgClass" {{ $attributes }} />
@endif
