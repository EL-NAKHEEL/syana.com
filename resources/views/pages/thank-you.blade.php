<x-layout class="page-thank-you">
    <x-page-header title="شكرًا، وصلنا طلبك" intro="هنكلمك في أقرب وقت على رقم الموبايل اللي كتبته." />
    <section class="section">
        <div class="container">
            @if ($order)
                <p data-track-onload="purchase" data-track-value="{{ $order['total'] }}" data-track-transaction="{{ $order['number'] }}">
                    رقم طلبك: <strong class="ltr">{{ $order['number'] }}</strong> — الإجمالي: <strong>{{ number_format($order['total']) }} جنيه</strong>.
                    هنكلمك نأكد الطلب وميعاد التوصيل والتركيب.
                </p>
            @else
                <p data-track-onload="generate_lead">هنكلمك نحدد معاد المعاينة.</p>
            @endif
            <div class="btn-row">
                <a class="btn btn-primary" href="{{ route('home') }}">الرئيسية</a>
                <x-call-button location="thank_you" label="محتاج حاجة عاجلة؟" />
            </div>
        </div>
    </section>
</x-layout>
