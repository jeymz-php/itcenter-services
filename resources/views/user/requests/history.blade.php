@extends('user.requests._layout')
@section('title','My Requests | IT Center')
@section('page-title','My Requests')
@section('page-sub','All your submitted service requests')

@section('request-content')

@if($errors->any())
<div class="abox err" style="margin-bottom:16px">
  <i class="fa-solid fa-triangle-exclamation"></i>
  <div>@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
</div>
@endif

@php
  $statusClass = [
    'pending'    => 'tag-pend',
    'approved'   => 'tag-appr',
    'processing' => 'tag-res',
    'completed'  => 'tag-done',
    'rejected'   => 'tag-rej',
    'cancelled'  => 'tag-arch',
  ];
@endphp

{{-- VIEW DETAIL MODAL --}}
<div class="modal-bg" id="detailModal" role="dialog" aria-modal="true" aria-labelledby="detailModalTitle">
  <div class="modal-box" style="max-width:680px">
    <div class="modal-hd">
      <h3 id="detailModalTitle"><i class="fa-solid fa-eye" style="color:var(--g600);margin-right:6px"></i>Request Details</h3>
      <button class="modal-close" type="button" onclick="closeModal('detailModal')" aria-label="Close request details"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body" id="modal-body-content" style="padding:0" aria-live="polite">
      <div style="padding:28px;text-align:center;color:var(--gray400)">
        <i class="fa-solid fa-circle-info" style="font-size:1.2rem;margin-bottom:8px;display:block"></i>
        Select a request to view its complete details.
      </div>
    </div>
  </div>
</div>

<div style="display:flex;flex-direction:column;gap:12px">
  @forelse($requests as $r)
  <div style="background:var(--white);border-radius:14px;border:1.5px solid var(--gray200);box-shadow:var(--shadow-sm);overflow:hidden;transition:box-shadow .2s"
    onmouseover="this.style.boxShadow='var(--shadow-md)'"
    onmouseout="this.style.boxShadow='var(--shadow-sm)'">

    {{-- Card Header --}}
    <div style="padding:14px 18px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;
      border-left:4px solid {{ $r->service_type==='printing'?'var(--blue)':($r->service_type==='photocopy'?'var(--orange)':'var(--g500)') }}">

      <div style="display:flex;align-items:center;gap:12px">
        {{-- Service Icon --}}
        <div style="width:40px;height:40px;border-radius:10px;flex-shrink:0;
          background:{{ $r->service_type==='printing'?'var(--blue-bg)':($r->service_type==='photocopy'?'var(--orange-bg)':'var(--g100)') }};
          display:flex;align-items:center;justify-content:center;
          color:{{ $r->service_type==='printing'?'var(--blue)':($r->service_type==='photocopy'?'var(--orange)':'var(--g600)') }};
          font-size:1rem">
          <i class="fa-solid {{ $r->service_type==='printing'?'fa-print':($r->service_type==='photocopy'?'fa-copy':'fa-desktop') }}"></i>
        </div>
        <div>
          <div style="font-size:.88rem;font-weight:800;color:var(--gray800)">
            {{ ucfirst($r->service_type) }} Request
          </div>
          <div style="font-size:.72rem;color:var(--gray400);font-family:monospace;margin-top:1px">
            {{ $r->request_number }}
          </div>
        </div>
      </div>

      <div style="display:flex;align-items:center;gap:10px">
        <span class="tag {{ $statusClass[$r->status] ?? '' }}">{{ strtoupper($r->status) }}</span>
        <span style="font-size:.7rem;color:var(--gray400)">{{ $r->created_at->format('M d, Y') }}</span>
      </div>
    </div>

    {{-- Card Body --}}
    <div style="padding:12px 18px 14px;display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px;border-top:1px solid var(--gray100)">
      <div style="display:flex;gap:24px;flex-wrap:wrap">

        @if($r->service_type === 'printing')
        <div>
          <div style="font-size:.63rem;color:var(--gray400);font-weight:700;text-transform:uppercase;margin-bottom:2px">Paper Size</div>
          <div style="font-size:.8rem;font-weight:700;color:var(--gray800)">{{ strtoupper($r->paper_size) }}</div>
        </div>
        <div>
          <div style="font-size:.63rem;color:var(--gray400);font-weight:700;text-transform:uppercase;margin-bottom:2px">Copies</div>
          <div style="font-size:.8rem;font-weight:700;color:var(--gray800)">{{ $r->copies }}</div>
        </div>
        <div>
          <div style="font-size:.63rem;color:var(--gray400);font-weight:700;text-transform:uppercase;margin-bottom:2px">Print Type</div>
          <div style="font-size:.8rem;font-weight:700;color:var(--gray800)">{{ ucfirst(str_replace('_',' ',$r->print_type ?? '')) }}</div>
        </div>
        <div>
          <div style="font-size:.63rem;color:var(--gray400);font-weight:700;text-transform:uppercase;margin-bottom:2px">File</div>
          <div style="font-size:.78rem;color:var(--blue)">{{ $r->file_name ?? '—' }}</div>
        </div>

        @elseif($r->service_type === 'photocopy')
        <div>
          <div style="font-size:.63rem;color:var(--gray400);font-weight:700;text-transform:uppercase;margin-bottom:2px">Paper Size</div>
          <div style="font-size:.8rem;font-weight:700;color:var(--gray800)">{{ strtoupper($r->paper_size) }}</div>
        </div>
        <div>
          <div style="font-size:.63rem;color:var(--gray400);font-weight:700;text-transform:uppercase;margin-bottom:2px">Copies</div>
          <div style="font-size:.8rem;font-weight:700;color:var(--gray800)">{{ $r->copies }}</div>
        </div>

        @else
        <div>
          <div style="font-size:.63rem;color:var(--gray400);font-weight:700;text-transform:uppercase;margin-bottom:2px">Duration</div>
          <div style="font-size:.8rem;font-weight:700;color:var(--gray800)">{{ $r->duration_minutes }} minutes</div>
        </div>
        @if($r->computer)
        <div>
          <div style="font-size:.63rem;color:var(--gray400);font-weight:700;text-transform:uppercase;margin-bottom:2px">PC Assigned</div>
          <div style="font-size:.8rem;font-weight:700;color:var(--g700)">{{ $r->computer->name }}</div>
        </div>
        @endif
        @if($r->computerSession)
        <div>
          <div style="font-size:.63rem;color:var(--gray400);font-weight:700;text-transform:uppercase;margin-bottom:2px">Session</div>
          <div style="font-size:.78rem;font-weight:700;color:var(--gray800)">
            {{ $r->computerSession->started_at?->format('g:i A') }} –
            {{ $r->computerSession->ended_at?->format('g:i A') ?? ($r->computerSession->ends_at?->format('g:i A').' (ends)') }}
          </div>
        </div>
        @endif
        @endif

        <div>
          <div style="font-size:.63rem;color:var(--gray400);font-weight:700;text-transform:uppercase;margin-bottom:2px">Purpose</div>
          <div style="font-size:.78rem;color:var(--gray700);max-width:220px">{{ Str::limit($r->purpose, 60) }}</div>
        </div>

      </div>

      {{-- Actions --}}
      <div style="display:flex;flex-direction:column;gap:6px;align-items:flex-end">
        <a href="{{ route('requests.show', $r) }}"
          class="request-detail-link"
          data-details-url="{{ route('requests.show', ['serviceRequest' => $r, 'modal' => 1]) }}"
          style="background:var(--g100);color:var(--g700);border:1.5px solid var(--g300);border-radius:8px;padding:7px 14px;font-size:.75rem;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:5px;white-space:nowrap;text-decoration:none">
          <i class="fa-solid fa-eye"></i> View Details
        </a>

        @if($r->service_type === 'printing' && $r->status === 'pending')
        <a href="{{ route('requests.printing.edit', $r) }}"
          style="background:var(--blue-bg);color:var(--blue);border:1.5px solid #90caf9;border-radius:8px;padding:7px 14px;font-size:.75rem;font-weight:700;display:flex;align-items:center;gap:5px;white-space:nowrap;text-decoration:none">
          <i class="fa-solid fa-pen-to-square"></i> Edit File & Details
        </a>
        @endif

        <a href="{{ route('requests.report', $r->id) }}" target="_blank"
          style="background:var(--white);color:var(--g700);border:1.5px solid var(--g300);border-radius:8px;padding:7px 14px;font-size:.75rem;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:5px;white-space:nowrap;text-decoration:none">
          <i class="fa-solid fa-file-pdf"></i> Generate Receipt
        </a>

        @if($r->status === 'rejected' && $r->admin_note)
        <div style="font-size:.68rem;color:var(--red);background:var(--red-bg);border-radius:6px;padding:5px 9px;max-width:180px;text-align:right">
          <i class="fa-solid fa-circle-xmark"></i> {{ Str::limit($r->admin_note, 50) }}
        </div>
        @endif

        @if($r->status === 'approved' && $r->service_type === 'research')
        <div style="font-size:.7rem;background:var(--g100);color:var(--g700);border-radius:6px;padding:5px 9px;text-align:right">
          <i class="fa-solid fa-circle-info"></i> Visit IT Center to start
        </div>
        @endif
      </div>
    </div>

  </div>
  @empty
  <div style="background:var(--white);border-radius:14px;padding:40px;text-align:center;color:var(--gray400);border:1.5px solid var(--gray200)">
    <i class="fa-solid fa-inbox" style="font-size:2rem;display:block;margin-bottom:10px"></i>
    <div style="font-size:.85rem;font-weight:700;margin-bottom:6px">No requests yet</div>
    <div style="font-size:.78rem">Use the sidebar to submit your first request.</div>
  </div>
  @endforelse
</div>

<div style="margin-top:16px">{{ $requests->links() }}</div>

@push('scripts')
<script>
(function () {
  const modal = document.getElementById('detailModal');
  const content = document.getElementById('modal-body-content');
  let activeController = null;

  function loadingState() {
    return `
      <div style="padding:34px;text-align:center;color:var(--gray400)">
        <i class="fa-solid fa-spinner fa-spin" style="font-size:1.35rem;margin-bottom:9px;display:block;color:var(--g600)"></i>
        Loading request details...
      </div>`;
  }

  function errorState(message) {
    return `
      <div style="padding:28px">
        <div class="abox err" style="margin:0">
          <i class="fa-solid fa-triangle-exclamation"></i>
          <div>${message}</div>
        </div>
      </div>`;
  }

  async function loadRequestDetails(link) {
    const url = link.dataset.detailsUrl || link.href;

    if (activeController) activeController.abort();
    activeController = new AbortController();

    content.innerHTML = loadingState();
    openModal('detailModal');

    try {
      const response = await fetch(url, {
        method: 'GET',
        headers: {
          'Accept': 'text/html',
          'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin',
        cache: 'no-store',
        signal: activeController.signal
      });

      if (response.redirected && response.url.includes('/login')) {
        throw new Error('Your session expired. Please refresh the page and sign in again.');
      }

      if (!response.ok) {
        if (response.status === 403) throw new Error('You are not allowed to view this request.');
        if (response.status === 404) throw new Error('This request could not be found.');
        if (response.status === 419) throw new Error('Your session expired. Please refresh the page and sign in again.');
        throw new Error('Unable to load the request details. Please try again.');
      }

      content.innerHTML = await response.text();
    } catch (error) {
      if (error.name === 'AbortError') return;
      content.innerHTML = errorState(error.message || 'Unable to load the request details.');
    }
  }

  document.querySelectorAll('.request-detail-link').forEach(link => {
    link.addEventListener('click', event => {
      // Keep Ctrl/Cmd-click, middle-click, and Shift-click working as normal links.
      if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || event.button !== 0) return;
      event.preventDefault();
      loadRequestDetails(link);
    });
  });

  if (modal) {
    modal.addEventListener('click', event => {
      if (event.target === modal && activeController) activeController.abort();
    });

    modal.querySelector('.modal-close')?.addEventListener('click', () => {
      if (activeController) activeController.abort();
    });
  }

  document.addEventListener('keydown', event => {
    if (event.key === 'Escape' && modal?.classList.contains('open')) {
      closeModal('detailModal');
      if (activeController) activeController.abort();
    }
  });
})();
</script>
@endpush
@endsection