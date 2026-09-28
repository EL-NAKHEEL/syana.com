<x-layout class="page-reviews">
    <x-page-header title="آراء العملاء" intro="آراء حقيقية من عملائنا، بتتنشر بعد المراجعة." />

    <section class="section">
        <div class="container">
            @if (session('status'))
                <p class="notice" role="status">{{ session('status') }}</p>
            @endif

            @if ($services->isNotEmpty())
                <form method="get" action="{{ route('reviews.index') }}" class="form review-filter">
                    <label for="review-service">عرض آراء خدمة</label>
                    <select id="review-service" name="service" onchange="this.form.requestSubmit()">
                        <option value="">كل الخدمات</option>
                        @foreach ($services as $service)
                            <option value="{{ $service->slug }}" @selected($currentService?->id === $service->id)>{{ $service->name }}</option>
                        @endforeach
                    </select>
                    <noscript><button class="btn btn-dark" type="submit">عرض</button></noscript>
                </form>
            @endif

            @if ($reviews->isEmpty())
                <p class="stack-top">لسه مفيش آراء منشورة. كن أول واحد يشاركنا رأيه!</p>
            @else
                <ul class="review-grid stack-top">
                    @foreach ($reviews as $review)
                        <x-review-card :review="$review" />
                    @endforeach
                </ul>
                {{ $reviews->links('store.pagination') }}
            @endif
        </div>
    </section>

    <section class="section section--light" aria-labelledby="review-form-title" id="review-form">
        <div class="container booking">
            <div>
                <span class="eyebrow">شاركنا رأيك</span>
                <h2 id="review-form-title">اكتب تقييمك</h2>
                <p>رأيك بيتنشر بعد المراجعة، ومش بننشر رقمك أو بياناتك.</p>
            </div>
            <form class="form" method="post" action="{{ route('reviews.store') }}" novalidate>
                @csrf
                <x-honeypot />
                @if ($errors->any())
                    <div class="form__errors" role="alert"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif
                <div class="form__grid">
                    <div class="form__field"><label for="r-name">الاسم *</label><input id="r-name" name="name" required maxlength="80" value="{{ old('name') }}"></div>
                    <div class="form__field">
                        <label for="r-rating">التقييم *</label>
                        <select id="r-rating" name="rating" required>
                            @foreach ([5, 4, 3, 2, 1] as $stars)<option value="{{ $stars }}" @selected((int) old('rating', 5) === $stars)>{{ $stars }} من 5</option>@endforeach
                        </select>
                    </div>
                    @if ($services->isNotEmpty())
                        <div class="form__field">
                            <label for="r-service">الخدمة</label>
                            <select id="r-service" name="service_id"><option value="">—</option>@foreach ($services as $service)<option value="{{ $service->id }}" @selected((int) old('service_id') === $service->id)>{{ $service->name }}</option>@endforeach</select>
                        </div>
                    @endif
                    @if ($products->isNotEmpty())
                        <div class="form__field">
                            <label for="r-product">التكييف اللي اشتريته</label>
                            <select id="r-product" name="product_id"><option value="">—</option>@foreach ($products as $product)<option value="{{ $product->id }}" @selected((int) old('product_id') === $product->id)>{{ $product->descriptiveName() }}</option>@endforeach</select>
                        </div>
                    @endif
                    @if ($areas->isNotEmpty())
                        <div class="form__field">
                            <label for="r-area">المنطقة</label>
                            <select id="r-area" name="area_id"><option value="">—</option>@foreach ($areas as $area)<option value="{{ $area->id }}" @selected((int) old('area_id') === $area->id)>{{ $area->name_ar }}</option>@endforeach</select>
                        </div>
                    @endif
                    <div class="form__field"><label for="r-before">الحرارة قبل (اختياري)</label><input id="r-before" name="temp_before" type="number" min="10" max="55" inputmode="numeric" value="{{ old('temp_before') }}"></div>
                    <div class="form__field"><label for="r-after">الحرارة بعد (اختياري)</label><input id="r-after" name="temp_after" type="number" min="10" max="55" inputmode="numeric" value="{{ old('temp_after') }}"></div>
                    <div class="form__field form__field--wide"><label for="r-body">رأيك *</label><textarea id="r-body" name="body" rows="4" required minlength="20" maxlength="2000">{{ old('body') }}</textarea></div>
                    <div class="form__field form__field--wide"><label for="r-video">رابط فيديو على يوتيوب (اختياري)</label><input id="r-video" name="video_url" type="url" dir="ltr" value="{{ old('video_url') }}"></div>
                </div>
                <button class="btn btn-primary btn-lg" type="submit">ابعت التقييم</button>
            </form>
        </div>
    </section>
</x-layout>
