@php
    app(\App\Seo\Seo::class)
        ->title('الصفحة غير موجودة')
        ->description('الصفحة اللي بتدور عليها مش موجودة أو اتنقلت. ارجع للرئيسية أو كلّم النخيل كوول على 01055207525 وهنساعدك توصل للي محتاجه في التكييف.')
        ->noindex();
@endphp
<x-layout class="page-error">
    <header class="page-head">
        <div class="container">
            <h1>الصفحة دي مش موجودة</h1>
            <p>ممكن تكون اتنقلت أو الرابط فيه غلطة. جرّب الرئيسية أو كلّمنا مباشرة.</p>
        </div>
    </header>
    <section class="section section--paper">
        <div class="container btn-row">
            <a class="btn btn--primary" href="{{ route('home') }}">الرئيسية</a>
            <x-call-button variant="ink" location="404" />
        </div>
    </section>
</x-layout>
