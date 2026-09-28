@php use App\Support\Copy; @endphp
<x-layout class="page-post">
    <x-page-header :title="$post->title" :intro="$post->excerpt" />

    <article class="section">
        <div class="container post-layout">
            <div>
                <p class="post-meta">
                    بقلم <a href="{{ $post->author->url() }}">{{ $post->author->name }}</a>
                    @if ($post->reviewer?->isPublished()) · راجعه <a href="{{ $post->reviewer->url() }}">{{ $post->reviewer->name }}</a>@endif
                    · نُشر <time datetime="{{ $post->published_at?->toDateString() }}">{{ $post->published_at?->locale('ar')->translatedFormat('j F Y') }}</time>
                    @if ($post->lastModified() && $post->published_at && $post->lastModified()->gt($post->published_at->copy()->addDay()))
                        · آخر تحديث <time datetime="{{ $post->lastModified()->toDateString() }}">{{ $post->lastModified()->locale('ar')->translatedFormat('j F Y') }}</time>
                    @endif
                    · {{ $post->readingTime() }}
                </p>

                @if ($image = $post->getFirstMedia('featured'))
                    <img class="post-hero" src="{{ $image->getUrl('large') }}" alt="{{ $image->getCustomProperty('alt') ?: $post->title }}" width="1280" height="720" fetchpriority="high">
                @endif

                <div class="prose post-body">{{ $body }}</div>

                @if ($post->service?->isLive())
                    <x-contact-band :heading="'محتاج '.$post->service->name.'؟'" :text="$post->service->summary" class="stack-top" />
                    <p class="stack-top"><a href="{{ $post->service->url() }}">اعرف أكتر عن {{ $post->service->name }}</a></p>
                @endif

                <aside class="author-box stack-top" aria-label="عن الكاتب">
                    @if ($avatar = $post->author->getFirstMediaUrl('photo', 'avatar'))
                        <img src="{{ $avatar }}" alt="{{ $post->author->name }}" width="96" height="96" loading="lazy">
                    @endif
                    <div>
                        <p><strong><a href="{{ $post->author->url() }}">{{ $post->author->name }}</a></strong> @if ($post->author->job_title)— {{ $post->author->job_title }}@endif</p>
                        @if ($post->author->bio)<p>{{ Copy::text($post->author->bio) }}</p>@endif
                    </div>
                </aside>
            </div>

            @if (count($toc) > 1)
                <nav class="toc" aria-labelledby="toc-title">
                    <h2 id="toc-title">محتوى المقال</h2>
                    <ol>
                        @foreach ($toc as $item)
                            <li @class(['toc__sub' => $item['level'] === 3])><a href="#{{ $item['id'] }}">{{ $item['text'] }}</a></li>
                        @endforeach
                    </ol>
                </nav>
            @endif
        </div>
    </article>

    @if ($related->isNotEmpty())
        <section class="section section--light" aria-labelledby="related-posts">
            <div class="container">
                <h2 id="related-posts">مقالات ذات صلة</h2>
                <ul class="link-list">
                    @foreach ($related as $item)
                        <li><a href="{{ $item->url() }}">{{ $item->title }}</a></li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif
</x-layout>
