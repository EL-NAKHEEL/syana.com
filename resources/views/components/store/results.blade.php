{{-- Results region (swapped in place by filters.js). --}}
@props(['products', 'chips'])
<div class="results" data-results aria-live="polite">
    <div class="results__bar">
        <p class="results__count">{{ $products->total() }} موديل</p>
        @if (count($chips))
            <ul class="chips" aria-label="الفلاتر الحالية">
                @foreach ($chips as $chip)
                    <li><a href="{{ $chip['url'] }}" rel="nofollow" aria-label="شيل فلتر {{ $chip['label'] }}">{{ $chip['label'] }} ×</a></li>
                @endforeach
            </ul>
        @endif
    </div>

    @if ($products->isEmpty())
        <p class="results__empty">مفيش موديلات بالمواصفات دي. جرّب تشيل فلتر أو <a href="tel:{{ config('site.phone.e164') }}" data-track="click_call" data-track-location="empty_results">اتصل بينا</a> ونساعدك.</p>
    @else
        <ul class="product-grid">
            @foreach ($products as $product)
                <x-store.product-card :product="$product" />
            @endforeach
        </ul>
        {{ $products->onEachSide(1)->links('store.pagination') }}
    @endif
</div>
