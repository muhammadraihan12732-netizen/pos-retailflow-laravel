@props(['paginator'])
<div class="pager">
    <span>Halaman {{ $paginator->currentPage() }} dari {{ max($paginator->lastPage(), 1) }}</span>
    <div class="pager-btns">
        @if ($paginator->onFirstPage())
            <span class="btn btn-outline disabled">Sebelumnya</span>
        @else
            <a class="btn btn-outline" href="{{ $paginator->previousPageUrl() }}">Sebelumnya</a>
        @endif

        @if ($paginator->hasMorePages())
            <a class="btn btn-outline" href="{{ $paginator->nextPageUrl() }}">Berikutnya</a>
        @else
            <span class="btn btn-outline disabled">Berikutnya</span>
        @endif
    </div>
</div>
