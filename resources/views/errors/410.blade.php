@php
    app(\App\Seo\Seo::class)
        ->title('المحتوى ده اتشال')
        ->description('المحتوى ده اتشال نهائيًا من موقع النخيل كوول. ارجع للرئيسية أو كلّمنا على 01055207525 لو محتاج بيع أو تركيب أو صيانة تكييف.')
        ->noindex();
@endphp
<x-layout class="page-error">
    <header class="page-head">
        <div class="container">
            <h1>المحتوى ده اتشال</h1>
            <p>الصفحة أو الصورة دي مش متاحة تاني.</p>
        </div>
    </header>
    <section class="section section--paper">
        <div class="container btn-row">
            <a class="btn btn--primary" href="{{ route('home') }}">الرئيسية</a>
        </div>
    </section>
</x-layout>
