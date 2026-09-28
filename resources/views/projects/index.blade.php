@php use App\Support\Copy; @endphp
<x-layout class="page-projects">
    <x-page-header title="مشاريعنا" intro="شغل حقيقي نفذناه، بالصور والتفاصيل." />
    <section class="section">
        <div class="container">
            <ul class="post-grid">
                @foreach ($projects as $project)
                    <li class="post-card">
                        @if ($photo = $project->getFirstMedia('photos'))
                            <img src="{{ $photo->getUrl('card') }}" alt="{{ $photo->getCustomProperty('alt') ?: $project->title }}" width="640" height="480" loading="lazy" decoding="async">
                        @endif
                        <div class="post-card__body">
                            <p class="eyebrow">{{ \App\Models\Project::CLIENT_TYPES[$project->client_type] ?? '' }}</p>
                            <h2><a href="{{ $project->url() }}">{{ $project->title }}</a></h2>
                            <p>{{ Copy::text($project->summary) }}</p>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>
</x-layout>
