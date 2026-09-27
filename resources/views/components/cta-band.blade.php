@props(['heading' => 'محتاج فني تكييف؟', 'text' => 'كلّمنا أو ابعت واتساب، وهنحدد معاك معاد المعاينة.'])
<div {{ $attributes->class(['cta-band']) }}>
    <div>
        <h2>{{ $heading }}</h2>
        <p>{{ \App\Support\Copy::text($text) }}</p>
    </div>
    <div class="btn-row">
        <x-call-button variant="ink" location="cta_band" />
        <x-whatsapp-button variant="paper" location="cta_band" />
    </div>
</div>
