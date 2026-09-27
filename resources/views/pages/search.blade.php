<x-layout class="page-search">
    <x-page-header title="البحث" />
    <section class="section">
        <div class="container">
            <form class="form search-form" method="get" action="{{ route('search') }}" role="search">
                <label for="search-q" class="visually-hidden">ابحث</label>
                <input id="search-q" name="q" type="search" value="{{ $query }}" placeholder="مثال: تكييف 1.5 حصان إنفرتر">
                <button class="btn btn-primary" type="submit">ابحث</button>
            </form>

            @if ($query !== '')
                @if ($services->isEmpty() && $products->isEmpty())
                    <p class="stack-top">مفيش نتايج لـ «{{ $query }}». جرّب كلمة تانية أو <a href="tel:{{ config('site.phone.e164') }}" data-track="click_call" data-track-location="search">اتصل بينا</a>.</p>
                @endif
                @if ($services->isNotEmpty())
                    <h2 class="stack-top">خدمات</h2>
                    <ul class="link-list">
                        @foreach ($services as $service)
                            <li><a href="{{ $service->url() }}">{{ $service->name }}</a></li>
                        @endforeach
                    </ul>
                @endif
                @if ($products->isNotEmpty())
                    <h2 class="stack-top">تكييفات</h2>
                    <ul class="product-grid">
                        @foreach ($products as $product)
                            <x-store.product-card :product="$product" />
                        @endforeach
                    </ul>
                @endif
            @endif
        </div>
    </section>
</x-layout>
