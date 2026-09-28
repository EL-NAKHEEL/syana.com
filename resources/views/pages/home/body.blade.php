@php use App\Support\Copy; @endphp
@if ($page->body)
    <section class="section">
        <div class="container prose">{{ Copy::html($page->body) }}</div>
    </section>
@endif
