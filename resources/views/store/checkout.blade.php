@php $areas = \App\Models\Area::live(); @endphp
<x-layout class="page-checkout">
    <x-page-header title="إتمام الطلب" />

    <section class="section">
        <div class="container two-col checkout">
            <form class="form" method="post" action="{{ route('checkout.store') }}" novalidate>
                @csrf
                @if ($errors->any())
                    <div class="form__errors" role="alert">
                        <p>راجع البيانات دي من فضلك:</p>
                        <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif

                <div class="form__grid">
                    <div class="form__field">
                        <label for="co-name">الاسم <span aria-hidden="true">*</span></label>
                        <input id="co-name" name="name" type="text" autocomplete="name" required value="{{ old('name') }}" @error('name') aria-invalid="true" @enderror>
                    </div>
                    <div class="form__field">
                        <label for="co-phone">رقم الموبايل <span aria-hidden="true">*</span></label>
                        <input id="co-phone" name="phone" type="tel" dir="ltr" inputmode="tel" autocomplete="tel" required placeholder="01xxxxxxxxx" value="{{ old('phone') }}" @error('phone') aria-invalid="true" @enderror>
                    </div>
                    <div class="form__field">
                        @if ($areas->isNotEmpty())
                            <label for="co-area">المنطقة <span aria-hidden="true">*</span></label>
                            <select id="co-area" name="area_id">
                                <option value="">اختار المنطقة</option>
                                @foreach ($areas as $area)
                                    <option value="{{ $area->id }}" @selected((int) old('area_id') === $area->id)>{{ $area->name_ar }}</option>
                                @endforeach
                            </select>
                            <label for="co-area-text">أو اكتب منطقتك</label>
                        @else
                            <label for="co-area-text">المنطقة <span aria-hidden="true">*</span></label>
                        @endif
                        <input id="co-area-text" name="area_text" type="text" autocomplete="address-level2" value="{{ old('area_text') }}">
                    </div>
                    <div class="form__field form__field--wide">
                        <label for="co-address">العنوان بالتفصيل <span aria-hidden="true">*</span></label>
                        <textarea id="co-address" name="address" rows="2" autocomplete="street-address" required>{{ old('address') }}</textarea>
                    </div>
                    <div class="form__field form__field--wide">
                        <label for="co-notes">ملاحظات</label>
                        <textarea id="co-notes" name="notes" rows="2">{{ old('notes') }}</textarea>
                    </div>
                    <fieldset class="form__field form__field--wide filters__group">
                        <legend>طريقة الدفع</legend>
                        @foreach ($gateways as $key => $gateway)
                            <label class="filters__check"><input type="radio" name="payment_method" value="{{ $key }}" @checked(old('payment_method', array_key_first($gateways)) === $key)> {{ $gateway->label() }}</label>
                            <p>{{ $gateway->description() }}</p>
                        @endforeach
                    </fieldset>
                </div>
                <button class="btn btn-primary btn-lg" type="submit">تأكيد الطلب</button>
            </form>

            <aside class="cart-summary" aria-labelledby="summary-title" data-track-onload="begin_checkout">
                <h2 id="summary-title">ملخص الطلب</h2>
                @foreach ($lines as $line)
                    <p>{{ $line['qty'] }} × {{ $line['product']->descriptiveName(false) }} — {{ number_format($line['line_total']) }} جنيه</p>
                @endforeach
                <p>الإجمالي: <span class="cart-summary__total">{{ number_format($subtotal) }} جنيه</span></p>
                <p>التوصيل والتركيب: هنأكد معاك التكلفة والميعاد في مكالمة التأكيد.</p>
            </aside>
        </div>
    </section>
</x-layout>
