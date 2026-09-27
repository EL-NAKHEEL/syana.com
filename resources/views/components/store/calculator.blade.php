{{-- «تكييف كام حصان لأوضتك؟» — a GET form (works without JS), shown only once the coefficients are confirmed. --}}
@php
    use App\Catalog\Catalog;
    use App\Catalog\Facet;
    $settings = app(\App\Settings\CalculatorSettings::class);
    $room = (int) request()->query('room');
    $result = $room >= 4 && $room <= 200 ? $settings->recommend($room, request()->query('sun') === '1', request()->query('top') === '1') : null;
@endphp
@if ($settings->confirmed)
    <section class="calculator" aria-labelledby="calc-title" id="calculator">
        <h2 id="calc-title">تكييف كام حصان لأوضتك؟</h2>
        <form method="get" action="{{ url()->current() }}#calculator" class="calculator__form">
            <label for="calc-room">مساحة الأوضة (متر مربع)</label>
            <input id="calc-room" name="room" type="number" min="4" max="200" inputmode="numeric" required value="{{ $room ?: '' }}">
            <label class="filters__check"><input type="checkbox" name="sun" value="1" @checked(request()->query('sun') === '1')> الأوضة عليها شمس كتير</label>
            <label class="filters__check"><input type="checkbox" name="top" value="1" @checked(request()->query('top') === '1')> آخر دور (تحت السطح)</label>
            <button class="btn btn-primary" type="submit">احسب</button>
        </form>
        @if ($result && $result['hp'])
            <p class="calculator__result" role="status">
                محتاج تقريبًا <strong class="ltr">{{ number_format($result['btu']) }} BTU</strong>، يعني
                <a href="{{ Facet::capacity($result['hp'])->url() }}">تكييف {{ Catalog::hpLabel($result['hp']) }} حصان</a>.
                الفني بيأكد القدرة المناسبة في المعاينة.
            </p>
        @endif
    </section>
@endif
