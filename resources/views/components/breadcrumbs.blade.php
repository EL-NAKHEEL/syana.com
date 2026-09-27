@props(['items'])
@if (count($items) > 1)
    <nav class="breadcrumbs" aria-label="مسار الصفحة">
        <ol>
            @foreach ($items as $item)
                <li>
                    @if ($loop->last)
                        <span aria-current="page">{{ $item['name'] }}</span>
                    @else
                        <a href="{{ $item['url'] }}">{{ $item['name'] }}</a>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
