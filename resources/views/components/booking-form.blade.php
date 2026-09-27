{{-- Service booking request (service, area and contact pages). Works without JS; server-side validation. --}}
@props(['service' => null, 'area' => null, 'heading' => 'احجز معاينة'])
@php
    $services = \App\Models\Service::query()->live()->get(['id', 'name']);
    $areas = \App\Models\Area::query()->published()->orderBy('sort')->get(['id', 'name_ar']);
@endphp
<section {{ $attributes->class(['section', 'section--light']) }} aria-labelledby="booking-title" id="booking">
    <div class="container booking">
        <div class="booking__intro">
            <span class="eyebrow">طلب معاينة</span>
            <h2 id="booking-title">{{ $heading }}</h2>
            <p>اكتب بياناتك وهنكلمك نحدد معاد المعاينة. أو اتصل مباشرة على <a href="tel:{{ config('site.phone.e164') }}" data-track="click_call" data-track-location="booking"><span class="ltr">{{ config('site.phone.display') }}</span></a>.</p>
        </div>

        <form class="form" method="post" action="{{ route('bookings.store') }}" novalidate>
            @csrf
            <x-honeypot />

            @if ($errors->any())
                <div class="form__errors" role="alert">
                    <p>راجع البيانات دي من فضلك:</p>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="form__grid">
                <div class="form__field">
                    <label for="booking-name">الاسم <span aria-hidden="true">*</span></label>
                    <input id="booking-name" name="name" type="text" autocomplete="name" required maxlength="120" value="{{ old('name') }}" @error('name') aria-invalid="true" @enderror>
                </div>
                <div class="form__field">
                    <label for="booking-phone">رقم الموبايل <span aria-hidden="true">*</span></label>
                    <input id="booking-phone" name="phone" type="tel" inputmode="tel" autocomplete="tel" dir="ltr" required placeholder="01xxxxxxxxx" value="{{ old('phone') }}" @error('phone') aria-invalid="true" @enderror>
                </div>
                <div class="form__field">
                    <label for="booking-service">الخدمة</label>
                    <select id="booking-service" name="service_id">
                        <option value="">اختار الخدمة</option>
                        @foreach ($services as $option)
                            <option value="{{ $option->id }}" @selected((int) old('service_id', $service?->id) === $option->id)>{{ $option->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form__field">
                    <label for="booking-ac-type">نوع التكييف</label>
                    <select id="booking-ac-type" name="ac_type">
                        <option value="">اختار النوع</option>
                        @foreach (\App\Models\BookingRequest::AC_TYPES as $value => $label)
                            <option value="{{ $value }}" @selected(old('ac_type') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form__field">
                    @if ($areas->isNotEmpty())
                        <label for="booking-area">المنطقة</label>
                        <select id="booking-area" name="area_id">
                            <option value="">اختار المنطقة</option>
                            @foreach ($areas as $option)
                                <option value="{{ $option->id }}" @selected((int) old('area_id', $area?->id) === $option->id)>{{ $option->name_ar }}</option>
                            @endforeach
                        </select>
                    @else
                        <label for="booking-area-text">المنطقة</label>
                        <input id="booking-area-text" name="area_text" type="text" autocomplete="address-level2" maxlength="120" value="{{ old('area_text') }}">
                    @endif
                </div>
                <div class="form__field">
                    <label for="booking-date">الميعاد المفضل</label>
                    <input id="booking-date" name="preferred_date" type="date" min="{{ now()->toDateString() }}" value="{{ old('preferred_date') }}">
                </div>
                <div class="form__field form__field--wide">
                    <label for="booking-notes">ملاحظات</label>
                    <textarea id="booking-notes" name="notes" rows="3" maxlength="1000">{{ old('notes') }}</textarea>
                </div>
            </div>

            <button class="btn btn-primary btn-lg" type="submit">ابعت طلب المعاينة</button>
        </form>
    </div>
</section>
