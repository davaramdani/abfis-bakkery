@if($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi halaman" class="flex items-center justify-between gap-2">
        @if($paginator->onFirstPage())
            <span class="admin-icon-button opacity-35" aria-disabled="true" aria-label="Halaman sebelumnya"><x-icon name="arrow-left" class="size-3.5" /></span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="admin-icon-button" aria-label="Halaman sebelumnya"><x-icon name="arrow-left" class="size-3.5" /></a>
        @endif
        <div class="flex items-center gap-1.5">
            @foreach($elements as $element)
                @if(is_string($element))<span class="px-1 text-xs text-muted">{{ $element }}</span>@endif
                @if(is_array($element))
                    @foreach($element as $page => $url)
                        @if($page === $paginator->currentPage())
                            <span class="admin-icon-button border-ink bg-ink text-cream" aria-current="page" aria-label="Halaman {{ $page }}">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="admin-icon-button text-xs" aria-label="Ke halaman {{ $page }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach
        </div>
        @if($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="admin-icon-button" aria-label="Halaman berikutnya"><x-icon name="arrow-right" class="size-3.5" /></a>
        @else
            <span class="admin-icon-button opacity-35" aria-disabled="true" aria-label="Halaman berikutnya"><x-icon name="arrow-right" class="size-3.5" /></span>
        @endif
    </nav>
@endif
