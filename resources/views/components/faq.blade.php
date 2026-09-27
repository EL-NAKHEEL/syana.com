@props(['faqs', 'heading' => 'أسئلة شائعة', 'id' => 'faq'])
@if (count($faqs))
    <section {{ $attributes->class(['section']) }} aria-labelledby="{{ $id }}-title">
        <div class="container">
            <h2 id="{{ $id }}-title">{{ $heading }}</h2>
            <div class="faq">
                @foreach ($faqs as $faq)
                    <details>
                        <summary>{{ $faq->question }}</summary>
                        <div>{{ \App\Support\Copy::text($faq->answer) }}</div>
                    </details>
                @endforeach
            </div>
        </div>
    </section>
@endif
