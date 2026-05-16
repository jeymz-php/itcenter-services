@extends('layouts.app')
@section('title','Manage Requests | Admin')
@section('body-class','dash-page')
@section('content')

{{-- REJECT MODAL --}}
<div class="modal-bg" id="rejectModal">
  <div class="modal-box">
    <div class="modal-hd">
      <h3><i class="fa-solid fa-xmark" style="color:var(--red);margin-right:6px"></i>Reject Request</h3>
      <button class="modal-close" onclick="closeModal('rejectModal')">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <form id="rejectForm" method="POST">
      @csrf
      <div class="modal-body">
        <div class="fg">
          <div class="flabel">Reason for Rejection</div>
          <textarea name="admin_note" class="fc" rows="3"
            placeholder="State the reason for rejection..." required style="resize:vertical"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="modal-btn secondary" onclick="closeModal('rejectModal')">Cancel</button>
        <button type="submit" class="modal-btn danger">
          <i class="fa-solid fa-xmark"></i> Reject
        </button>
      </div>
    </form>
  </div>
</div>

<div class="dash-wrap">
  @include('admin.partials.sidebar')
  <main class="main">
    @include('admin.partials.topbar', [
      'title' => 'Manage Requests',
      'sub'   => 'Printing, Photocopy & Research service requests',
    ])
    <div class="content">

      @if(session('success'))
        <div class="abox ok" style="margin-bottom:14px">
          <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
      @endif

      {{-- STATUS TABS --}}
      <div class="tab-pills">
        @foreach([
          ''           => 'All',
          'pending'    => 'Pending',
          'approved'   => 'Approved',
          'processing' => 'Processing',
          'completed'  => 'Completed',
          'rejected'   => 'Rejected',
        ] as $val => $label)
        <a href="{{ route('admin.service-requests.index', array_merge(request()->query(), ['status' => $val])) }}"
           class="tab-pill {{ request('status') === $val ? 'active' : '' }}">
          {{ $label }}
          <span class="cnt">{{ $counts[$val === '' ? 'all' : $val] ?? 0 }}</span>
        </a>
        @endforeach
      </div>

      {{-- FILTER BAR --}}
      <form class="filter-bar" method="GET" action="{{ route('admin.service-requests.index') }}">
        <input type="hidden" name="status" value="{{ request('status') }}">
        <input type="text" name="search" class="fc"
          placeholder="🔍 Request #, user name, ID number..."
          value="{{ request('search') }}">
        <div class="sw" style="min-width:160px">
          <select name="service_type" class="fs">
            <option value="">All Services</option>
            <option value="printing"  {{ request('service_type')==='printing' ?'selected':'' }}>Printing</option>
            <option value="photocopy" {{ request('service_type')==='photocopy'?'selected':'' }}>Photocopy</option>
            <option value="research"  {{ request('service_type')==='research' ?'selected':'' }}>Research</option>
          </select>
        </div>
        <div class="sw" style="min-width:170px">
          <select name="campus" class="fs">
            <option value="">All Campuses</option>
            @foreach(config('campuses') as $k => $v)
            <option value="{{ $k }}" {{ request('campus') === $k ? 'selected' : '' }}>{{ $v }}</option>
            @endforeach
          </select>
        </div>
        <button type="submit" class="btn-sm">
          <i class="fa-solid fa-magnifying-glass"></i> Filter
        </button>
        <a href="{{ route('admin.service-requests.index') }}" class="btn-outline">Reset</a>
      </form>

      {{-- TABLE --}}
      <div class="tbl-wrap">
        <table>
          <thead>
            <tr>
              <th>REQUEST #</th>
              <th>USER</th>
              <th>SERVICE</th>
              <th>DETAILS</th>
              <th>CAMPUS</th>
              <th>DATE</th>
              <th>STATUS</th>
              <th>ACTIONS</th>
            </tr>
          </thead>
          <tbody>
            @forelse($requests as $r)
            <tr>
              <td style="font-family:monospace;font-weight:700;font-size:.76rem">
                {{ $r->request_number }}
              </td>
              <td>
                <div style="display:flex;align-items:center;gap:8px">
                  <div class="sb-avatar" style="width:30px;height:30px;font-size:.68rem;flex-shrink:0">
                    @if($r->user->profile_picture)
                      <img src="{{ Storage::url($r->user->profile_picture) }}" alt="">
                    @else
                      {{ strtoupper(substr($r->user->first_name,0,1)) }}
                    @endif
                  </div>
                  <div>
                    <div style="font-size:.76rem;font-weight:700">{{ $r->user->full_name }}</div>
                    <div style="font-size:.64rem;color:var(--gray400)">{{ $r->user->id_number }}</div>
                  </div>
                </div>
              </td>
              <td>
                @php
                  $svcBg    = $r->service_type==='printing' ? 'var(--blue-bg)' : ($r->service_type==='photocopy' ? 'var(--orange-bg)' : 'var(--g100)');
                  $svcColor = $r->service_type==='printing' ? 'var(--blue)' : ($r->service_type==='photocopy' ? 'var(--orange)' : 'var(--g600)');
                  $svcIcon  = $r->service_type==='printing' ? 'fa-print' : ($r->service_type==='photocopy' ? 'fa-copy' : 'fa-desktop');
                @endphp
                <span class="tag" style="background:{{ $svcBg }};color:{{ $svcColor }}">
                  <i class="fa-solid {{ $svcIcon }}"></i> {{ ucfirst($r->service_type) }}
                </span>
              </td>
              <td style="font-size:.73rem;color:var(--gray600);max-width:160px">
                @if($r->service_type==='printing')
                  {{ $r->copies }}x · {{ strtoupper($r->paper_size) }} · {{ str_replace('_',' ',ucfirst($r->print_type ?? '')) }}
                @elseif($r->service_type==='photocopy')
                  {{ $r->copies }}x · {{ strtoupper($r->paper_size) }}
                @else
                  {{ $r->duration_minutes }} min PC
                  @if($r->computer) · {{ $r->computer->name }} @endif
                @endif
              </td>
              <td style="font-size:.73rem">{{ config('campuses.'.$r->user->campus) }}</td>
              <td style="font-size:.72rem;color:var(--gray600);white-space:nowrap">
                {{ $r->created_at->format('M d, Y') }}<br>
                <span style="font-size:.68rem">{{ $r->created_at->format('g:i A') }}</span>
              </td>
              <td>
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
                <span class="tag {{ $statusClass[$r->status] ?? '' }}">
                  {{ strtoupper($r->status) }}
                </span>
              </td>
              <td>
                <div style="display:flex;gap:4px;flex-wrap:wrap">
                  {{-- View --}}
                  <a href="{{ route('admin.service-requests.show', $r) }}"
                     class="act-btn act-view" title="View">
                    <i class="fa-solid fa-eye"></i>
                  </a>

                  {{-- Approve / Reject --}}
                  @if($r->status === 'pending')
                    <form action="{{ route('admin.service-requests.approve', $r) }}" method="POST" style="display:inline">
                      @csrf
                      <button type="submit" class="act-btn act-appr" title="Approve">
                        <i class="fa-solid fa-check"></i>
                      </button>
                    </form>
                    <button class="act-btn act-del" title="Reject"
                      onclick="openReject('{{ route('admin.service-requests.reject', $r) }}')">
                      <i class="fa-solid fa-xmark"></i>
                    </button>
                  @endif

                  {{-- Mark Processing (non-research) --}}
                  @if($r->status === 'approved' && $r->service_type !== 'research')
                    <form action="{{ route('admin.service-requests.processing', $r) }}" method="POST" style="display:inline">
                      @csrf
                      <button type="submit" class="act-btn act-edit" title="Mark Processing">
                        <i class="fa-solid fa-gear"></i>
                      </button>
                    </form>
                  @endif

                  {{-- Mark Complete --}}
                  @if($r->status === 'processing' && $r->service_type !== 'research')
                    <form action="{{ route('admin.service-requests.complete', $r) }}" method="POST" style="display:inline">
                      @csrf
                      <button type="submit" class="act-btn act-appr" title="Mark Completed">
                        <i class="fa-solid fa-check-double"></i>
                      </button>
                    </form>
                  @endif
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="8" style="text-align:center;padding:32px;color:var(--gray400)">
                <i class="fa-solid fa-inbox" style="display:block;font-size:1.5rem;margin-bottom:8px"></i>
                No service requests found.
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div style="margin-top:14px">{{ $requests->links() }}</div>

    </div>
  </main>
</div>

@push('scripts')
<script>
function openModal(id){ document.getElementById(id).classList.add('open') }
function closeModal(id){ document.getElementById(id).classList.remove('open') }
document.querySelectorAll('.modal-bg').forEach(m =>
  m.addEventListener('click', e => { if(e.target === m) m.classList.remove('open') })
);
function openReject(url){
  document.getElementById('rejectForm').setAttribute('action', url);
  openModal('rejectModal');
}
</script>
@endpush