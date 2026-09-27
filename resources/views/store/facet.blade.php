@php use App\Support\Copy; use App\Catalog\Catalog; @endphp
<x-layout class="page-facet">
    <x-page-header :title="$h1" :intro="$page?->intro" />

    {{-- Compact live price table above the grid (owner decision C2): prices are text, from the products table. --}}
    @if ($priceRows->isNotEmpty())
        <section class="section" aria-labelledby="facet-prices">
            <div class="container">
                <div class="section-title--center">
                    <span class="eyebrow">الأسعار</span>
                    <h2 id="facet-prices">أسعار {{ $facet->label() }}</h2>
                    @if ($updated)
                        <p>آخر تحديث: <time datetime="{{ $updated->toDateString() }}">{{ $updated->locale('ar')->translatedFormat('j F Y') }}</time></p>
                    @endif
                </div>
                <div class="table-wrap">
                    <table class="price-table">
                        <thead>
                            <tr>
                                <th scope="col">الموديل</th>
                                <th scope="col">القدرة</th>
                                <th scope="col">النوع</th>
                                <th scope="col">السعر</th>
                                <th scope="col">سعر العرض</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($priceRows as $row)
                                <tr>
                                    <th scope="row"><a href="{{ $row->url() }}">{{ $row->brand->name_ar }} <span class="ltr">{{ $row->model_number }}</span></a></th>
                                    <td>{{ Catalog::hpLabel($row->hp) }} حصان</td>
                                    <td>{{ Catalog::TYPES[$row->type] ?? $row->type }}</td>
                                    <td>@if ($row->onSale())<del>{{ number_format((float) $row->price) }}</del>@else<strong>{{ number_format((float) $row->price) }}</strong>@endif جنيه</td>
                                    <td>@if ($row->onSale())<strong>{{ number_format((float) $row->sale_price) }}</strong> جنيه @else — @endif</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    @endif

    <section class="section">
        @if ($facet->kind === 'capacity')
            <div class="container"><x-store.calculator /></div>
        @endif
        <div class="container store-layout">
            <x-store.filters :action="$formAction" :filters="$filters" :brand-options="$brandOptions"
                :hide-brand="$facet->brand !== null" :hide-capacity="$facet->hp !== null" :hide-type="$facet->type !== null" />
            <x-store.results :products="$products" :chips="$chips" />
        </div>
    </section>

    @if ($page?->body)
        <section class="section">
            <div class="container prose">{{ Copy::html($page->body) }}</div>
        </section>
    @endif

    @if ($page)
        <x-faq :faqs="$page->faqs" />
    @endif

    <section class="section section--light">
        <div class="container">
            <x-store.facet-links :links="$facetLinks" />
        </div>
    </section>
</x-layout>
