@if ($paginator->hasPages())
    <nav class="pager" role="navigation">
        @if ($paginator->onFirstPage())
            <span class="pager__link pager__link--disabled">←</span>
        @else
            <a class="pager__link" href="{{ $paginator->previousPageUrl() }}" rel="prev">←</a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="pager__gap">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="pager__link pager__link--current">{{ $page }}</span>
                    @else
                        <a class="pager__link" href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a class="pager__link" href="{{ $paginator->nextPageUrl() }}" rel="next">→</a>
        @else
            <span class="pager__link pager__link--disabled">→</span>
        @endif
    </nav>
@endif
