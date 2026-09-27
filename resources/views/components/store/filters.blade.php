{{-- Store filters: a plain GET form (works without JS). Filtered URLs are noindex with no canonical. --}}
@props(['action', 'filters', 'brandOptions', 'hideBrand' => false, 'hideCapacity' => false, 'hideType' => false])
@php use App\Catalog\Catalog; use App\Catalog\ProductFilters; @endphp
<details class="filters" data-filters open>
    <summary class="filters__toggle">الفلاتر والترتيب</summary>
    <form class="filters__form" method="get" action="{{ $action }}" data-filter-form>
        <div class="filters__group">
            <label for="filter-sort">الترتيب</label>
            <select id="filter-sort" name="sort">
                @foreach (ProductFilters::SORTS as $value => $label)
                    <option value="{{ $value }}" @selected($filters->sort === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        @if (! $hideBrand && count($brandOptions))
            <fieldset class="filters__group">
                <legend>الماركة</legend>
                @foreach ($brandOptions as $slug => $name)
                    <label class="filters__check"><input type="checkbox" name="brand[]" value="{{ $slug }}" @checked(in_array($slug, $filters->brands, true))> {{ $name }}</label>
                @endforeach
            </fieldset>
        @endif

        @if (! $hideCapacity)
            <fieldset class="filters__group">
                <legend>القدرة</legend>
                @foreach (Catalog::CAPACITIES as $slug => $hp)
                    <label class="filters__check"><input type="checkbox" name="hp[]" value="{{ $slug }}" @checked(in_array($slug, $filters->hp, true))> {{ Catalog::hpLabel($hp) }} حصان</label>
                @endforeach
            </fieldset>
        @endif

        @if (! $hideType)
            <fieldset class="filters__group">
                <legend>النوع</legend>
                @foreach (Catalog::TYPES as $slug => $name)
                    <label class="filters__check"><input type="checkbox" name="type[]" value="{{ $slug }}" @checked(in_array($slug, $filters->types, true))> {{ $name }}</label>
                @endforeach
            </fieldset>
        @endif

        <fieldset class="filters__group">
            <legend>التبريد</legend>
            <label class="filters__check"><input type="radio" name="cooling" value="" @checked($filters->cooling === null)> الكل</label>
            @foreach (Catalog::COOLING as $value => $label)
                <label class="filters__check"><input type="radio" name="cooling" value="{{ $value }}" @checked($filters->cooling === $value)> {{ $label }}</label>
            @endforeach
            <label class="filters__check"><input type="checkbox" name="inverter" value="1" @checked($filters->inverter)> إنفرتر بس</label>
        </fieldset>

        <fieldset class="filters__group filters__price">
            <legend>السعر (جنيه)</legend>
            <label>من <input type="number" name="price_min" min="0" step="500" inputmode="numeric" value="{{ $filters->priceMin }}"></label>
            <label>لحد <input type="number" name="price_max" min="0" step="500" inputmode="numeric" value="{{ $filters->priceMax }}"></label>
        </fieldset>

        <div class="btn-row">
            <button class="btn btn-primary" type="submit" data-filter-submit>عرض النتايج</button>
            <a class="btn btn-dark" href="{{ $action }}">مسح الفلاتر</a>
        </div>
    </form>
</details>
