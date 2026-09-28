@php use App\Support\Copy; @endphp
<x-layout class="page-project">
    <x-page-header :title="$project->title" :intro="$project->summary" />
    <section class="section">
        <div class="container">
            <ul class="link-list">
                <li><span>{{ \App\Models\Project::CLIENT_TYPES[$project->client_type] ?? '' }}</span></li>
                @if ($project->area?->isLive())<li><a href="{{ $project->area->url() }}">{{ $project->area->name_ar }}</a></li>@endif
                @if ($project->service?->isLive())<li><a href="{{ $project->service->url() }}">{{ $project->service->name }}</a></li>@endif
                @if ($project->completed_on)<li><span>{{ $project->completed_on->locale('ar')->translatedFormat('F Y') }}</span></li>@endif
            </ul>
            @if ($photos->isNotEmpty())
                <ul class="gallery stack-top">
                    @foreach ($photos as $photo)
                        <li><img src="{{ $photo->getUrl('large') }}" alt="{{ $photo->getCustomProperty('alt') ?: $project->title }}" width="1400" height="1050" @if ($loop->first) fetchpriority="high" @else loading="lazy" @endif decoding="async"></li>
                    @endforeach
                </ul>
            @endif
            @if ($project->story)
                <div class="prose stack-top">{{ Copy::html($project->story) }}</div>
            @endif
        </div>
    </section>
    <div class="container"><x-contact-band heading="عندك مشروع شبه ده؟" /></div>
</x-layout>
