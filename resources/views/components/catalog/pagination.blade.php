@props(['paginator'])

@if($paginator->hasPages())
    <nav class="catalog-pagination" aria-label="Pagination">
        @if($paginator->onFirstPage())
            <span class="catalog-pagination__button is-disabled" aria-disabled="true">← Previous</span>
        @else
            <a class="catalog-pagination__button" href="{{ $paginator->previousPageUrl() }}" rel="prev">← Previous</a>
        @endif

        <span class="catalog-pagination__status">
            Page {{ $paginator->currentPage() }} of {{ max(1, $paginator->lastPage()) }}
        </span>

        @if($paginator->hasMorePages())
            <a class="catalog-pagination__button" href="{{ $paginator->nextPageUrl() }}" rel="next">Next →</a>
        @else
            <span class="catalog-pagination__button is-disabled" aria-disabled="true">Next →</span>
        @endif
    </nav>
@endif
