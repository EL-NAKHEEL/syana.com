@php use App\Support\Copy; @endphp
<x-layout class="page-about">
    <x-page-header :title="$page->title" :intro="$page->intro" :media="$page->getFirstMedia('header')" />

    @if ($page->body)
        <section class="section">
            <div class="container two-col">
                <div class="prose">{{ Copy::html($page->body) }}</div>
                <x-image :media="$page->getFirstMedia('gallery')" fallback="technician-on-ladder-servicing-ac" alt="فني من النخيل كوول بيصين تكييف" sizes="(min-width: 992px) 600px, 100vw" />
            </div>
        </section>
    @endif

    <x-faq :faqs="$page->faqs" />

    <div class="container">
        <x-contact-band />
    </div>
</x-layout>
