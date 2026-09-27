@if ($paginator->hasPages())
    <nav class="pagination" aria-label="صفحات النتايج">
        <ul>
            @if (! $paginator->onFirstPage())
                <li><a href="{{ $paginator->previousPageUrl() }}" rel="prev">السابق</a></li>
            @endif
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li><span>{{ $element }}</span></li>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        <li>
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}">صفحة {{ $page }}</a>
                            @endif
                        </li>
                    @endforeach
                @endif
            @endforeach
            @if ($paginator->hasMorePages())
                <li><a href="{{ $paginator->nextPageUrl() }}" rel="next">التالي</a></li>
            @endif
        </ul>
    </nav>
@endif
