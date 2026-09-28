@php use App\Support\Copy; @endphp
<x-layout class="page-default">
    <x-page-header :title="$page->title" :intro="$page->intro" />
    <section class="section">
        <div class="container prose">{{ Copy::html($page->body) }}</div>
    </section>
    <x-faq :faqs="$page->faqs" />
</x-layout>
