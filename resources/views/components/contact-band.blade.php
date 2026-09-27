@props(['heading' => 'محتاج فني تكييف؟', 'text' => 'كلّمنا أو ابعت واتساب، وهنحدد معاك معاد المعاينة.'])
<div {{ $attributes->class(['contact-band']) }}>
    <div>
        <span class="eyebrow">اتصل بنا</span>
        <h2>{{ $heading }}</h2>
        <p>{{ \App\Support\Copy::text($text) }}</p>
    </div>
    <div class="btn-row">
        <x-call-button location="contact_band" size="lg" />
        <x-whatsapp-button location="contact_band" size="lg" />
    </div>
</div>
