@php use App\Support\Copy; use App\Catalog\Catalog; @endphp
<x-layout class="page-product">
    <section class="section product" data-track-onload="view_item">
        <div class="container">
            <nav class="breadcrumbs breadcrumbs--light" aria-label="مسار الصفحة">
                <ol>
                    @foreach (app(\App\Seo\Seo::class)->breadcrumbItems() as $item)
                        <li>@if ($loop->last)<span aria-current="page">{{ $item['name'] }}</span>@else<a href="{{ $item['url'] }}">{{ $item['name'] }}</a>@endif</li>
                    @endforeach
                </ol>
            </nav>

            <div class="two-col product__top">
                <div class="product__gallery">
                    <x-store.product-image :product="$product" size="large" :eager="true" />
                </div>

                <div class="product__summary">
                    <p class="eyebrow">{{ $product->brand->name_ar }}</p>
                    <h1>{{ $product->descriptiveName() }}</h1>
                    @if ($product->short_description)
                        <p>{{ Copy::text($product->short_description) }}</p>
                    @endif

                    <div class="product__price">
                        <strong>{{ number_format($product->currentPrice()) }} جنيه</strong>
                        @if ($product->onSale())
                            <del>{{ number_format((float) $product->price) }} جنيه</del>
                            @if ($product->sale_ends_at)
                                <span class="product__sale-ends">العرض لحد {{ $product->sale_ends_at->locale('ar')->translatedFormat('j F') }}</span>
                            @endif
                        @endif
                    </div>

                    <p @class(['product__stock', 'is-out' => ! $product->canBeOrdered()])>{{ Catalog::STOCK[$product->stock_status] }}</p>

                    <ul class="check-list">
                        <li>القدرة: {{ Catalog::hpLabel($product->hp) }} حصان @if ($product->btu)(<span class="ltr">{{ number_format($product->btu) }} BTU</span>)@endif</li>
                        <li>{{ Catalog::COOLING[$product->cooling] }} — {{ $product->is_inverter ? 'إنفرتر' : 'عادي (مش إنفرتر)' }}</li>
                        @if ($product->room_area_min && $product->room_area_max)
                            <li>مناسب لمساحة {{ $product->room_area_min }}–{{ $product->room_area_max }} متر تقريبًا</li>
                        @endif
                        @if ($product->warranty_months)
                            <li>الضمان: {{ $product->warranty_months }} شهر @if ($product->warranty_note)({{ $product->warranty_note }})@endif</li>
                        @endif
                        @if ($product->installation_included !== null)
                            <li>{{ $product->installation_included ? 'السعر شامل التركيب' : 'التركيب بيتحسب لوحده' }}</li>
                        @endif
                        @if ($product->installments_note)
                            <li>التقسيط: {{ $product->installments_note }}</li>
                        @endif
                    </ul>

                    @if ($product->canBeOrdered())
                        <form method="post" action="{{ route('cart.add') }}" class="product__buy">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <label for="qty" class="visually-hidden">الكمية</label>
                            <input id="qty" name="qty" type="number" min="1" max="{{ \App\Commerce\Cart::MAX_QTY }}" value="1" inputmode="numeric">
                            <button class="btn btn-primary btn-lg" type="submit" data-track="add_to_cart">أضف للسلة</button>
                        </form>
                    @else
                        <p>الموديل ده مش متوفر دلوقتي. شوف البدائل تحت أو اتصل بينا نشوف لك أقرب موديل.</p>
                    @endif
                    <div class="btn-row">
                        <x-call-button location="product" label="اسأل عن الموديل" />
                        <x-whatsapp-button location="product" :message="'السلام عليكم، عايز أسأل عن '.$product->descriptiveName()" />
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section section--light" aria-labelledby="product-specs">
        <div class="container">
            <h2 id="product-specs">المواصفات</h2>
            <div class="table-wrap">
                <table class="price-table spec-table">
                    <tbody>
                        <tr><th scope="row">الماركة</th><td>{{ $product->brand->name_ar }}</td></tr>
                        <tr><th scope="row">الموديل</th><td class="ltr">{{ $product->model_number }}</td></tr>
                        <tr><th scope="row">النوع</th><td>{{ Catalog::TYPES[$product->type] }}</td></tr>
                        <tr><th scope="row">القدرة</th><td>{{ Catalog::hpLabel($product->hp) }} حصان</td></tr>
                        @if ($product->btu)<tr><th scope="row">BTU</th><td>{{ number_format($product->btu) }}</td></tr>@endif
                        <tr><th scope="row">التبريد</th><td>{{ Catalog::COOLING[$product->cooling] }}</td></tr>
                        <tr><th scope="row">إنفرتر</th><td>{{ $product->is_inverter ? 'نعم' : 'لا' }}</td></tr>
                        @if ($product->energy_class)<tr><th scope="row">كفاءة الطاقة</th><td>{{ $product->energy_class }}</td></tr>@endif
                        @foreach ($product->specs ?? [] as $spec)
                            <tr><th scope="row">{{ $spec['label'] }}</th><td>{{ $spec['value'] }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    @if ($product->description)
        <section class="section">
            <div class="container prose">{{ Copy::html($product->description) }}</div>
        </section>
    @endif

    <x-reviews-section :reviews="$reviews" :heading="'آراء اللي اشتروا التكييف ده'" />

    <x-faq :faqs="$product->faqs" />

    @if ($related->isNotEmpty())
        <section class="section" aria-labelledby="product-related">
            <div class="container">
                <h2 id="product-related">{{ $product->canBeOrdered() ? 'موديلات ممكن تعجبك' : 'بدائل متوفرة' }}</h2>
                <ul class="product-grid">
                    @foreach ($related as $item)
                        <x-store.product-card :product="$item" />
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    <section class="section" aria-labelledby="product-more">
        <div class="container">
            <h2 id="product-more">تصفح أكتر</h2>
            <ul class="link-list">
                @foreach ($facets as $facet)
                    <li><a href="{{ $facet->url() }}">{{ $facet->label() }}</a></li>
                @endforeach
            </ul>
        </div>
    </section>
</x-layout>
