{{-- Extra text and FAQs the owner adds to a section page, shown under the listing. --}}
@props(['hub' => null])
@if ($hub?->body)
    <section class="section">
        <div class="container prose">{{ \App\Support\Copy::html($hub->body) }}</div>
    </section>
@endif
@if ($hub)
    <x-faq :faqs="$hub->faqs" />
@endif
