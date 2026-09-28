@props(['review'])
<li class="review-card">
    <p class="review-card__stars" aria-label="التقييم {{ $review->rating }} من 5">
        <span aria-hidden="true">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</span>
    </p>
    <blockquote>{{ $review->body }}</blockquote>
    @if ($review->temp_before && $review->temp_after)
        <p class="review-card__temps">الحرارة قبل: <span class="ltr">{{ $review->temp_before }}°</span> ← بعد: <span class="ltr">{{ $review->temp_after }}°</span></p>
    @endif
    <p class="review-card__author">
        <strong>{{ $review->name }}</strong>
        @if ($review->area?->isPublished())— {{ $review->area->name_ar }}@endif
        <time datetime="{{ $review->approved_at?->toDateString() }}">{{ $review->approved_at?->locale('ar')->translatedFormat('F Y') }}</time>
    </p>
</li>
