@php use App\Support\Copy; @endphp
<x-layout class="page-author">
    <x-page-header :title="$person->name" :intro="$person->job_title" />
    <section class="section">
        <div class="container">
            <div class="author-box">
                @if ($avatar = $person->getFirstMediaUrl('photo', 'avatar'))
                    <img src="{{ $avatar }}" alt="{{ $person->name }}" width="96" height="96">
                @endif
                <div>
                    @if ($person->bio)<p>{{ Copy::text($person->bio) }}</p>@endif
                    @if ($person->credentials)<p><strong>الخبرات والشهادات:</strong> {{ Copy::text($person->credentials) }}</p>@endif
                    @if ($person->years_experience)<p>{{ $person->years_experience }} سنة خبرة في التكييف</p>@endif
                </div>
            </div>
            <h2 class="stack-top">مقالات {{ $person->name }}</h2>
            <ul class="link-list">
                @foreach ($posts as $post)
                    <li><a href="{{ $post->url() }}">{{ $post->title }}</a></li>
                @endforeach
            </ul>
        </div>
    </section>
</x-layout>
