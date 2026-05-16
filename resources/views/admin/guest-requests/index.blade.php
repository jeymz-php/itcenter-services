@extends('layouts.app')
@section('title','Guest Requests | Admin')
@section('body-class','dash-page')
@section('content')

<div class="modal-bg" id="rejectModal">
  <div class="modal-box">
    <div class="modal-hd">
      <h3>Reject Guest Request</h3>
      <button class="modal-close" onclick="closeModal('rejectModal')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form id="rejectForm" method="POST">
      @csrf
      <div class="modal-body">
        <div class="fg"><div class="flabel">Reason</div>
          <textarea name="admin_note" class="fc" rows="3" required style="resize:vertical"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="modal-btn secondary" onclick="closeModal('rejectModal')">Cancel</button>
        <button type="submit" class="modal-btn danger">Reject</button>
      </div>
    </form>
  </div>
</div>

<div class="dash-wrap">
  @include('admin.partials.sidebar')
  <main class="main">
    @include('admin.partials.topbar', ['title'=>'Guest Requests','sub'=>'Walk-in and public service requests'])
    <div class="content">

      @if(session('success'))<div class="abox ok" style="margin-bottom:14px"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>@endif

      <div class="tab-pills">
        @foreach([''=> 'All','pending'=>'Pending','approved'=>'Approved','processing'=>'Processing','completed'=>'Completed','rejected'=>'Rejected'] as $v=>$l)
        <a href="{{ route('admin.guest-requests.index', array_merge(request()->query(),['status'=>$v])) }}"
           class="tab-pill {{ request('status')===$v?'active':'' }}">
          {{ $l }} <span class="cnt">{{ $counts[$v===''?'all':$v]??0 }}</span>
        </a>
        @endforeach
      </div>

      <form class="filter-bar" method="GET" action="{{ route('admin.guest-requests.index') }}">
        <input type="hidden" name="status" value="{{ request('status') }}">
        <input type="text" name="search" class="fc" placeholder="🔍 Request #, name, email..." value="{{ request('search') }}">
        <div class="sw" style="min-width:140px">
          <select name="service_type" class="fs">
            <option value="">All Services</option>
            <option value="printing"  {{ request('service_type')==='printing' ?'selected':'' }}>Printing</option>
            <option value="photocopy" {{ request('service_type')==='photocopy'?'selected':'' }}>Photocopy</option>
          </select>
        </div>
        <div class="sw" style="min-width:130px">
          <select name="role" class="fs">
            <option value="">All Roles</option>
            <option value="student"       {{ request('role')==='student'      ?'selected':'' }}>Student</option>
            <option value="faculty_staff" {{ request('role')==='faculty_staff'?'selected':'' }}>Faculty/Staff</option>
            <option value="visitor"       {{ request('role')==='visitor'      ?'selected':'' }}>Visitor</option>
          </select>
        </div>
        <button type="submit" class="btn-sm"><i class="fa-solid fa-magnifying-glass"></i> Filter</button>
        <a href="{{ route('admin.guest-requests.index') }}" class="btn-outline">Reset</a>
      </form>

      <div class="tbl-wrap">
        <table>
          <thead>
            <tr><th>REQ #</th><th>NAME</th><th>ROLE</th><th>SERVICE</th><th>CAMPUS</th><th>DATE</th><th>STATUS</th><th>ACTIONS</th></tr>
          </thead>
          <tbody>
            @forelse($requests as $r)
            <tr>
              <td style="font-family:monospace;font-weight:700;font-size:.75rem">{{ $r->request_number }}</td>
              <td>
                <div style="font-size:.76rem;font-weight:700">{{ $r->full_name }}</div>
                <div style="font-size:.64rem;color:var(--gray400)">{{ $r->email }}</div>
              </td>
              <td>
                <span class="tag {{ $r->role==='student'?'tag-student':($r->role==='visitor'?'tag-pend':'tag-faculty') }}">
                  {{ ucfirst(str_replace('_',' ',$r->role)) }}
                </span>
              </td>
              <td>
                <span class="tag" style="background:{{ $r->service_type==='printing'?'var(--blue-bg)':'var(--orange-bg)' }};color:{{ $r->service_type==='printing'?'var(--blue)':'var(--orange)' }}">
                  <i class="fa-solid {{ $r->service_type==='printing'?'fa-print':'fa-copy' }}"></i> {{ ucfirst($r->service_type) }}
                </span>
              </td>
              <td style="font-size:.73rem">{{ config('campuses.'.$r->campus) }}</td>
              <td style="font-size:.72rem;color:var(--gray600)">{{ $r->created_at->format('M d, Y') }}</td>
              <td>
                @php $sc=['pending'=>'tag-pend','approved'=>'tag-appr','processing'=>'tag-res','completed'=>'tag-done','rejected'=>'tag-rej'] @endphp
                <span class="tag {{ $sc[$r->status]??'' }}">{{ strtoupper($r->status) }}</span>
              </td>
              <td>
                <div style="display:flex;gap:4px">
                  <a href="{{ route('admin.guest-requests.show',$r) }}" class="act-btn act-view" title="View"><i class="fa-solid fa-eye"></i></a>
                  @if($r->status==='pending')
                    <form action="{{ route('admin.guest-requests.approve',$r) }}" method="POST" style="display:inline">@csrf<button class="act-btn act-appr" title="Approve"><i class="fa-solid fa-check"></i></button></form>
                    <button class="act-btn act-del" title="Reject" onclick="openReject('{{ route('admin.guest-requests.reject',$r) }}')"><i class="fa-solid fa-xmark"></i></button>
                  @endif
                  @if($r->status==='approved')
                    <form action="{{ route('admin.guest-requests.processing',$r) }}" method="POST" style="display:inline">@csrf<button class="act-btn act-edit" title="Processing"><i class="fa-solid fa-gear"></i></button></form>
                  @endif
                  @if($r->status==='processing')
                    <form action="{{ route('admin.guest-requests.complete',$r) }}" method="POST" style="display:inline">@csrf<button class="act-btn act-appr" title="Complete"><i class="fa-solid fa-check-double"></i></button></form>
                  @endif
                </div>
              </td>
            </tr>
            @empty
            <tr><td colspan="8" style="text-align:center;padding:28px;color:var(--gray400)">No guest requests found.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div style="margin-top:14px">{{ $requests->links() }}</div>
    </div>
  </main>
</div>
@endsection
@push('scripts')
<script>
function openModal(id){document.getElementById(id).classList.add('open')}
function closeModal(id){document.getElementById(id).classList.remove('open')}
document.querySelectorAll('.modal-bg').forEach(m=>m.addEventListener('click',e=>{if(e.target===m)m.classList.remove('open')}));
function openReject(url){document.getElementById('rejectForm').setAttribute('action',url);openModal('rejectModal')}
</script>
@endpush