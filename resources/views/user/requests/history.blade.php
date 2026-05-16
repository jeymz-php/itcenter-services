@extends('user.requests._layout')
@section('title','My Requests | IT Center')
@section('page-title','My Requests')
@section('page-sub','All your submitted service requests')

@section('request-content')

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
<div class="modal-bg" id="detailModal">
  <div class="modal-box" style="max-width:560px">
    <div class="modal-hd">
      <h3 id="modal-req-number" style="font-family:monospace"></h3>
      <button class="modal-close" onclick="closeModal('detailModal')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body" id="modal-body-content" style="padding:0">
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
        <button onclick="showDetail({{ $r->id }})"
          style="background:var(--g100);color:var(--g700);border:1.5px solid var(--g300);border-radius:8px;padding:7px 14px;font-size:.75rem;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:5px;white-space:nowrap">
          <i class="fa-solid fa-eye"></i> View Details
        </button>

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

{{-- Hidden data for modal --}}
@foreach($requests as $r)
<script>
window.requestData = window.requestData || {};
window.requestData[{{ $r->id }}] = {
  number:  '{{ $r->request_number }}',
  service: '{{ ucfirst($r->service_type) }}',
  status:  '{{ $r->status }}',
  date:    '{{ $r->created_at->format("M d, Y g:i A") }}',
  purpose: {{ json_encode($r->purpose) }},
  @if($r->service_type==='printing')
  paperSize: '{{ strtoupper($r->paper_size) }}',
  copies: '{{ $r->copies }}',
  printType: '{{ ucfirst(str_replace("_"," ",$r->print_type??'')) }}',
  fileName: {{ json_encode($r->file_name) }},
  @elseif($r->service_type==='photocopy')
  paperSize: '{{ strtoupper($r->paper_size) }}',
  copies: '{{ $r->copies }}',
  @else
  duration: '{{ $r->duration_minutes }} minutes',
  computer: '{{ $r->computer->name ?? "Not yet assigned" }}',
  sessionStart: '{{ $r->computerSession?->started_at?->format("g:i A") ?? "—" }}',
  sessionEnd: '{{ $r->computerSession?->ended_at?->format("g:i A") ?? ($r->computerSession?->ends_at?->format("g:i A") ?? "—") }}',
  @endif
  adminNote: {{ json_encode($r->admin_note) }},
};
</script>
@endforeach

@push('scripts')
<script>
function openModal(id){ document.getElementById(id).classList.add('open') }
function closeModal(id){ document.getElementById(id).classList.remove('open') }
document.querySelectorAll('.modal-bg').forEach(m=>m.addEventListener('click',e=>{if(e.target===m)m.classList.remove('open')}));

const statusColors = {
  pending:'tag-pend', approved:'tag-appr', processing:'tag-res',
  completed:'tag-done', rejected:'tag-rej', cancelled:'tag-arch'
};

function showDetail(id) {
  const d = window.requestData[id];
  if (!d) return;

  document.getElementById('modal-req-number').innerHTML =
    `<i class="fa-solid fa-receipt" style="color:var(--g600);margin-right:6px"></i>${d.number}`;

  let rows = `
    <div style="padding:16px 20px;border-bottom:1px solid var(--gray100);display:grid;grid-template-columns:1fr 1fr;gap:12px">
      <div>
        <div style="font-size:.63rem;color:var(--gray400);font-weight:700;text-transform:uppercase;margin-bottom:3px">Service</div>
        <div style="font-size:.84rem;font-weight:700;color:var(--gray800)">${d.service}</div>
      </div>
      <div>
        <div style="font-size:.63rem;color:var(--gray400);font-weight:700;text-transform:uppercase;margin-bottom:3px">Status</div>
        <span class="tag ${statusColors[d.status]||''}">${d.status.toUpperCase()}</span>
      </div>
      <div>
        <div style="font-size:.63rem;color:var(--gray400);font-weight:700;text-transform:uppercase;margin-bottom:3px">Date Submitted</div>
        <div style="font-size:.82rem;color:var(--gray800)">${d.date}</div>
      </div>`;

  if (d.paperSize) rows += `
      <div>
        <div style="font-size:.63rem;color:var(--gray400);font-weight:700;text-transform:uppercase;margin-bottom:3px">Paper Size</div>
        <div style="font-size:.82rem;font-weight:700;color:var(--gray800)">${d.paperSize}</div>
      </div>`;

  if (d.copies) rows += `
      <div>
        <div style="font-size:.63rem;color:var(--gray400);font-weight:700;text-transform:uppercase;margin-bottom:3px">Copies</div>
        <div style="font-size:.82rem;font-weight:700;color:var(--gray800)">${d.copies}</div>
      </div>`;

  if (d.printType) rows += `
      <div>
        <div style="font-size:.63rem;color:var(--gray400);font-weight:700;text-transform:uppercase;margin-bottom:3px">Print Type</div>
        <div style="font-size:.82rem;font-weight:700;color:var(--gray800)">${d.printType}</div>
      </div>`;

  if (d.fileName) rows += `
      <div style="grid-column:1/-1">
        <div style="font-size:.63rem;color:var(--gray400);font-weight:700;text-transform:uppercase;margin-bottom:3px">File</div>
        <div style="font-size:.8rem;color:var(--blue)">${d.fileName}</div>
      </div>`;

  if (d.duration) rows += `
      <div>
        <div style="font-size:.63rem;color:var(--gray400);font-weight:700;text-transform:uppercase;margin-bottom:3px">Duration</div>
        <div style="font-size:.82rem;font-weight:700;color:var(--gray800)">${d.duration}</div>
      </div>`;

  if (d.computer) rows += `
      <div>
        <div style="font-size:.63rem;color:var(--gray400);font-weight:700;text-transform:uppercase;margin-bottom:3px">PC Assigned</div>
        <div style="font-size:.82rem;font-weight:700;color:var(--g700)">${d.computer}</div>
      </div>`;

  if (d.sessionStart && d.sessionStart !== '—') rows += `
      <div>
        <div style="font-size:.63rem;color:var(--gray400);font-weight:700;text-transform:uppercase;margin-bottom:3px">Session Start</div>
        <div style="font-size:.82rem;color:var(--gray800)">${d.sessionStart}</div>
      </div>
      <div>
        <div style="font-size:.63rem;color:var(--gray400);font-weight:700;text-transform:uppercase;margin-bottom:3px">Session End</div>
        <div style="font-size:.82rem;color:var(--gray800)">${d.sessionEnd}</div>
      </div>`;

  rows += `
      <div style="grid-column:1/-1">
        <div style="font-size:.63rem;color:var(--gray400);font-weight:700;text-transform:uppercase;margin-bottom:3px">Purpose</div>
        <div style="font-size:.8rem;color:var(--gray700);line-height:1.5">${d.purpose || '—'}</div>
      </div>`;

  if (d.adminNote) rows += `
      <div style="grid-column:1/-1">
        <div style="font-size:.63rem;color:var(--red);font-weight:700;text-transform:uppercase;margin-bottom:3px">Admin Note</div>
        <div style="font-size:.8rem;color:var(--red);background:var(--red-bg);border-radius:7px;padding:8px 10px;line-height:1.5">${d.adminNote}</div>
      </div>`;

  rows += `</div>`;
  document.getElementById('modal-body-content').innerHTML = rows;
  openModal('detailModal');
}
</script>
@endpush
@endsection