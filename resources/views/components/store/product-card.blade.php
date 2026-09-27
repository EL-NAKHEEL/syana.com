@props(['product'])
<li class="product-card">
    <a class="product-card__media" href="{{ $product->url() }}" tabindex="-1" aria-hidden="true">
        <x-store.product-image :product="$product" />
    </a>
    <div class="product-card__body">
        <p class="product-card__brand">{{ $product->brand->name_ar }}</p>
        <h3 class="product-card__title"><a href="{{ $product->url() }}">{{ $product->descriptiveName(false) }}</a></h3>
        <p class="product-card__model ltr">{{ $product->model_number }}</p>
        <p class="product-card__price">
            <strong>{{ number_format($product->currentPrice()) }} جنيه</strong>
            @if ($product->onSale())
                <del>{{ number_format((float) $product->price) }} جنيه</del>
            @endif
        </p>
        <p @class(['product-card__stock', 'is-out' => ! $product->canBeOrdered()])>{{ \App\Catalog\Catalog::STOCK[$product->stock_status] }}</p>
    </div>
</li>
