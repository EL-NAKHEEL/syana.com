@php use App\Support\Copy; @endphp
<x-layout class="page-price-guide">
    <x-page-header :title="$guide->render($guide->h1)" :intro="$guide->intro" />

    <section class="section" aria-labelledby="price-table-title">
        <div class="container">
            <div class="section-title--center">
                <span class="eyebrow">الأسعار</span>
                <h2 id="price-table-title">جدول الأسعار</h2>
                @if ($updated)
                    <p>آخر تحديث: <time datetime="{{ $updated->toDateString() }}">{{ $updated->locale('ar')->translatedFormat('j F Y') }}</time></p>
                @endif
            </div>
            <div class="table-wrap">
                <table class="price-table">
                    <thead>
                        <tr>
                            <th scope="col">الخدمة</th>
                            <th scope="col">يبدأ من</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $service)
                            <tr>
                                <th scope="row"><a href="{{ $service->url() }}">{{ $service->name }}</a></th>
                                <td>
                                    @if ($service->starting_price !== null)
                                        <strong>{{ number_format((float) $service->starting_price) }}</strong> جنيه
                                    @else
                                        {{ $service->price_note ?: 'السعر بعد المعاينة' }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="stack-top">الأسعار دي بداية التكلفة، والسعر النهائي بيتحدد بعد المعاينة حسب حالة التكييف والشغل المطلوب.</p>
        </div>
    </section>

    @if ($guide->body)
        <section class="section">
            <div class="container prose">{{ Copy::html($guide->body) }}</div>
        </section>
    @endif

    <x-faq :faqs="$guide->faqs" />

    <div class="container">
        <x-contact-band heading="عايز سعر دقيق؟" text="الفني يعاين ويقولك التكلفة بالظبط قبل أي شغل." />
    </div>
</x-layout>
