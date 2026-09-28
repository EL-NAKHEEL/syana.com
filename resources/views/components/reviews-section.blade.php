{{-- Approved reviews for a product/service/area. Visible reviews only; never marked up for the business. --}}
@props(['reviews', 'heading' => 'آراء العملاء'])
@if ($reviews->isNotEmpty())
    <section class="section section--light" aria-labelledby="reviews-title">
        <div class="container">
            <div class="section-title--center">
                <span class="eyebrow">آراء العملاء</span>
                <h2 id="reviews-title">{{ $heading }}</h2>
            </div>
            <ul class="review-grid">
                @foreach ($reviews as $review)
                    <x-review-card :review="$review" />
                @endforeach
            </ul>
            <p class="stack-top"><a href="{{ route('reviews.index') }}">كل آراء العملاء</a></p>
        </div>
    </section>
@endif
