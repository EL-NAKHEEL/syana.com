{{-- Product photo from the media library (WebP card/large), or the temporary placeholder. --}}
@props(['product', 'size' => 'card', 'eager' => false])
@php $media = $product->primaryImage(); @endphp
@if ($media)
    <img src="{{ $media->getUrl($size) }}"
         srcset="{{ $media->getUrl('card') }} 480w, {{ $media->getUrl('large') }} 1000w"
         sizes="{{ $size === 'card' ? '(min-width: 992px) 300px, 50vw' : '(min-width: 992px) 600px, 100vw' }}"
         alt="{{ $media->getCustomProperty('alt') ?: $product->descriptiveName() }}"
         width="{{ $size === 'card' ? 480 : 1000 }}" height="{{ $size === 'card' ? 480 : 1000 }}"
         @if ($eager) loading="eager" fetchpriority="high" @else loading="lazy" @endif decoding="async" {{ $attributes }}>
@else
    <x-picture name="ac-units-square" :alt="$product->descriptiveName()" :sizes="$size === 'card' ? '(min-width: 992px) 300px, 50vw' : '(min-width: 992px) 600px, 100vw'" :eager="$eager" {{ $attributes }} />
@endif
