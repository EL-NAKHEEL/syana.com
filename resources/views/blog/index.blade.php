@php use App\Support\Copy; @endphp
<x-layout class="page-blog">
    <x-hub-header :hub="$hub" :title="$heading" :intro="$intro" />
    <section class="section">
        <div class="container">
            @if ($categories->isNotEmpty())
                <ul class="link-list">
                    @foreach ($categories as $category)
                        <li><a href="{{ $category->url() }}">{{ $category->name }}</a></li>
                    @endforeach
                </ul>
            @endif
            <ul class="post-grid stack-top">
                @foreach ($posts as $post)
                    <li class="post-card">
                        @if ($image = $post->getFirstMedia('featured'))
                            <img src="{{ $image->getUrl('card') }}" alt="{{ $image->getCustomProperty('alt') ?: $post->title }}" width="640" height="360" loading="lazy" decoding="async">
                        @endif
                        <div class="post-card__body">
                            @if ($post->category?->isPublished())<p class="eyebrow">{{ $post->category->name }}</p>@endif
                            <h2><a href="{{ $post->url() }}">{{ $post->title }}</a></h2>
                            <p>{{ Copy::text($post->excerpt) }}</p>
                            <p class="post-card__meta">{{ $post->author->name }} · {{ $post->readingTime() }}</p>
                        </div>
                    </li>
                @endforeach
            </ul>
            {{ $posts->links('store.pagination') }}
        </div>
    </section>
    @if ($posts->currentPage() === 1)
        <x-hub-extra :hub="$hub" />
    @endif
</x-layout>
