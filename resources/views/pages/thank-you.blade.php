<x-layout class="page-thank-you">
    <x-page-header title="شكرًا، وصلنا طلبك" intro="هنكلمك في أقرب وقت على رقم الموبايل اللي كتبته." />
    <section class="section" data-track-onload="{{ $type === 'order' ? 'purchase' : 'generate_lead' }}">
        <div class="container btn-row">
            <a class="btn btn-primary" href="{{ route('home') }}">الرئيسية</a>
            <x-call-button location="thank_you" label="محتاج حاجة عاجلة؟" />
        </div>
    </section>
</x-layout>
