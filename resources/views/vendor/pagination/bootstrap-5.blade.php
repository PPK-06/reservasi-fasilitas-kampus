{{--
    Override view paginasi Bootstrap 5 bawaan Laravel (aktif lewat
    Paginator::useBootstrapFive()). Dipakai otomatis oleh semua ->links().
    Ringkasan di kiri, tombol halaman di kanan; mb-0 menetralkan margin
    bawaan <ul>/<p> supaya keduanya sejajar.
--}}
@if ($paginator->hasPages())
    <nav class="w-100" aria-label="Navigasi halaman">
        {{-- Layar sempit: Sebelumnya | Hal. x dari y | Berikutnya --}}
        <div class="d-flex d-sm-none align-items-center justify-content-between gap-2">
            <ul class="pagination mb-0">
                @if ($paginator->onFirstPage())
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="page-link">@lang('pagination.previous')</span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">@lang('pagination.previous')</a>
                    </li>
                @endif
            </ul>

            <span class="small text-muted text-nowrap">
                Hal. {{ $paginator->currentPage() }} dari {{ $paginator->lastPage() }}
            </span>

            <ul class="pagination mb-0">
                @if ($paginator->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">@lang('pagination.next')</a>
                    </li>
                @else
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="page-link">@lang('pagination.next')</span>
                    </li>
                @endif
            </ul>
        </div>

        {{-- Layar sm ke atas: ringkasan kiri, nomor halaman kanan --}}
        <div class="d-none d-sm-flex flex-wrap align-items-center justify-content-between gap-2 gap-md-3">
            <p class="small text-muted mb-0">
                Menampilkan
                <span class="fw-semibold">{{ $paginator->firstItem() }}</span>&ndash;<span class="fw-semibold">{{ $paginator->lastItem() }}</span>
                dari
                <span class="fw-semibold">{{ $paginator->total() }}</span>
                data
            </p>

            <ul class="pagination flex-wrap mb-0">
                @if ($paginator->onFirstPage())
                    <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                        <span class="page-link" aria-hidden="true">&lsaquo;</span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')">&lsaquo;</a>
                    </li>
                @endif

                @foreach ($elements as $element)
                    @if (is_string($element))
                        <li class="page-item disabled" aria-disabled="true"><span class="page-link">{{ $element }}</span></li>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                            @else
                                <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                @if ($paginator->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')">&rsaquo;</a>
                    </li>
                @else
                    <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                        <span class="page-link" aria-hidden="true">&rsaquo;</span>
                    </li>
                @endif
            </ul>
        </div>
    </nav>
@endif
