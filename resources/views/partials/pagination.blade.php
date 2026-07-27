@if ($paginator->hasPages())
<nav style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">

  {{-- Prev --}}
  @if ($paginator->onFirstPage())
    <span style="padding:8px 16px;border-radius:8px;background:var(--gray100);color:var(--gray400);font-size:.78rem;font-weight:700;cursor:not-allowed">
      &laquo; Previous
    </span>
  @else
    <a href="{{ $paginator->previousPageUrl() }}" style="padding:8px 16px;border-radius:8px;background:var(--white);color:var(--g700);border:1.5px solid var(--gray200);font-size:.78rem;font-weight:700;text-decoration:none">
      &laquo; Previous
    </a>
  @endif

  {{-- Page numbers --}}
  <div style="display:flex;align-items:center;gap:5px;flex-wrap:wrap">
    @foreach ($elements as $element)
      @if (is_string($element))
        <span style="padding:6px 10px;color:var(--gray400);font-size:.76rem">{{ $element }}</span>
      @endif

      @if (is_array($element))
        @foreach ($element as $page => $url)
          @if ($page == $paginator->currentPage())
            <span style="padding:7px 13px;border-radius:8px;background:var(--g600);color:var(--white);font-size:.78rem;font-weight:700">{{ $page }}</span>
          @else
            <a href="{{ $url }}" style="padding:7px 13px;border-radius:8px;background:var(--white);color:var(--gray700);border:1.5px solid var(--gray200);font-size:.78rem;font-weight:700;text-decoration:none">{{ $page }}</a>
          @endif
        @endforeach
      @endif
    @endforeach
  </div>

  {{-- Next --}}
  @if ($paginator->hasMorePages())
    <a href="{{ $paginator->nextPageUrl() }}" style="padding:8px 16px;border-radius:8px;background:var(--white);color:var(--g700);border:1.5px solid var(--gray200);font-size:.78rem;font-weight:700;text-decoration:none">
      Next &raquo;
    </a>
  @else
    <span style="padding:8px 16px;border-radius:8px;background:var(--gray100);color:var(--gray400);font-size:.78rem;font-weight:700;cursor:not-allowed">
      Next &raquo;
    </span>
  @endif

</nav>

@if ($paginator->total() > 0)
<div style="margin-top:10px;font-size:.72rem;color:var(--gray400)">
  Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }} of {{ $paginator->total() }} results
</div>
@endif
@endif