{{-- Crawlable links to the curated facets only (descriptive Arabic anchors). --}}
@props(['links'])
@if (count($links['brands']) || count($links['capacities']) || count($links['types']))
    <nav class="facet-links" aria-label="تصفح المتجر">
        @foreach (['brands' => 'حسب الماركة', 'capacities' => 'حسب القدرة', 'types' => 'حسب النوع'] as $key => $title)
            @if (count($links[$key]))
                <div>
                    <h2>{{ $title }}</h2>
                    <ul class="link-list">
                        @foreach ($links[$key] as $link)
                            <li><a href="{{ $link['url'] }}">{{ $link['label'] }}</a></li>
                        @endforeach
                    </ul>
                </div>
            @endif
        @endforeach
    </nav>
@endif
