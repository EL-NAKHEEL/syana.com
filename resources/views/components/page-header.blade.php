{{-- Inner-page header from the existing site: photo + navy gradient, the page H1 and breadcrumbs. --}}
@props(['title', 'intro' => null, 'image' => 'engineers-reviewing-site-plans'])
<header class="page-header">
    <x-picture :name="$image" alt="" sizes="100vw" :eager="true" />
    <div class="page-header__inner">
        <div class="container">
            <h1>{{ $title }}</h1>
            @if ($intro)
                <p class="page-header__intro">{{ \App\Support\Copy::text($intro) }}</p>
            @endif
            <x-breadcrumbs :items="app(\App\Seo\Seo::class)->breadcrumbItems()" />
        </div>
    </div>
</header>
