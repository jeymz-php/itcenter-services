@extends('layouts.app')
@section('title','User Details | Admin')
@section('body-class','dash-page')
@section('content')

<div class="dash-wrap">
  @include('admin.partials.sidebar')
  <main class="main">
    @include('admin.partials.topbar', ['title' => 'User Details', 'sub' => 'Full account information and request history'])
    <div class="content">

      @if(session('success'))<div class="abox ok" style="margin-bottom:14px"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>@endif

      <div style="display:grid;grid-template-columns:320px 1fr;gap:16px;align-items:start">

        <!-- LEFT: Profile Card -->
        <div>
          <div class="profile-card">
            <div class="profile-card-hd"><i class="fa-solid fa-circle-user"></i> Profile</div>
            <div class="profile-card-body" style="text-align:center">
              <div style="width:80px;height:80px;border-radius:50%;overflow:hidden;background:var(--g500);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:1.8rem;margin:0 auto 12px">
                @if($user->profile_picture)<img src="{{ Storage::url($user->profile_picture) }}" style="width:100%;height:100%;object-fit:cover">@else{{ strtoupper(substr($user->first_name,0,1)) }}@endif
              </div>
              <div style="font-size:1rem;font-weight:800;color:var(--gray800)">{{ $user->full_name }}</div>
              <div style="font-size:.75rem;color:var(--gray600);margin-top:3px">{{ $user->email }}</div>
              <div style="margin-top:8px;display:flex;justify-content:center;gap:6px;flex-wrap:wrap">
                <span class="tag {{ $user->user_type=='student'?'tag-student':'tag-faculty' }}">{{ ucfirst(str_replace('_',' ',$user->user_type)) }}</span>
                @php $sc=['pending'=>'tag-pend','active'=>'tag-active','deactivated'=>'tag-deact','archived'=>'tag-arch','rejected'=>'tag-rej'] @endphp
                <span class="tag {{ $sc[$user->status]??'' }}">{{ strtoupper($user->status) }}</span>
              </div>
            </div>
          </div>

          <div class="profile-card" style="margin-top:14px">
            <div class="profile-card-hd"><i class="fa-solid fa-info-circle"></i> Details</div>
            <div class="profile-card-body" style="padding:0">
              @foreach([['ID Number',$user->id_number,'fa-id-card'],['Campus',config('campuses.'.$user->campus),'fa-building-columns'],['Registered',$user->created_at->format('M d, Y g:i A'),'fa-calendar']] as [$lbl,$val,$ico])
              <div style="padding:10px 18px;border-bottom:1px solid var(--gray100);display:flex;align-items:center;gap:10px">
                <i class="fa-solid {{ $ico }}" style="color:var(--g600);width:14px;font-size:.8rem"></i>
                <div>
                  <div style="font-size:.66rem;color:var(--gray400);font-weight:600;text-transform:uppercase">{{ $lbl }}</div>
                  <div style="font-size:.78rem;font-weight:600;color:var(--gray800);margin-top:1px">{{ $val }}</div>
                </div>
              </div>
              @endforeach
            </div>
          </div>

          <!-- Quick Actions -->
          <div class="profile-card" style="margin-top:14px">
            <div class="profile-card-hd"><i class="fa-solid fa-bolt"></i> Actions</div>
            <div class="profile-card-body" style="display:flex;flex-direction:column;gap:8px;padding:14px 18px">
              @if($user->status==='pending')
                <form action="{{ route('admin.users.approve',$user) }}" method="POST">@csrf<button class="btn" style="padding:9px"><i class="fa-solid fa-user-check"></i> Approve Account</button></form>
                <button class="btn" style="background:linear-gradient(135deg,var(--red),#c62828);padding:9px" onclick="openModal('rejectModal')"><i class="fa-solid fa-user-xmark"></i> Reject Account</button>
              @endif
              @if($user->status==='active')
                <button class="btn" style="background:linear-gradient(135deg,#7b1fa2,#ab47bc);padding:9px" onclick="openModal('deactModal')"><i class="fa-solid fa-user-slash"></i> Deactivate</button>
                <form action="{{ route('admin.users.archive',$user) }}" method="POST">@csrf<button class="btn" style="background:linear-gradient(135deg,var(--gray600),var(--gray400));padding:9px"><i class="fa-solid fa-box-archive"></i> Archive</button></form>
              @endif
              @if($user->status==='deactivated')
                <form action="{{ route('admin.users.activate',$user) }}" method="POST">@csrf<button class="btn" style="padding:9px"><i class="fa-solid fa-user-check"></i> Activate</button></form>
              @endif
            </div>
          </div>
        </div>

        <!-- RIGHT -->
        <div>
          <!-- Pending Account Requests -->
          @php $pendingReqs = $user->accountRequests()->where('status','pending')->get(); @endphp
          @if($pendingReqs->count())
          <div class="abox warn" style="margin-bottom:14px;flex-direction:column;align-items:flex-start;gap:10px">
            <div style="display:flex;align-items:center;gap:8px;font-weight:700"><i class="fa-solid fa-bell"></i> Pending Account Requests ({{ $pendingReqs->count() }})</div>
            @foreach($pendingReqs as $req)
            <div style="background:rgba(255,255,255,.6);border-radius:8px;padding:10px 14px;width:100%">
              <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px">
                <div>
                  <span class="tag tag-pend" style="margin-right:8px">{{ strtoupper($req->type) }} REQUEST</span>
                  <span style="font-size:.75rem;color:var(--gray600)">{{ $req->created_at->format('M d, Y') }}</span>
                  <div style="font-size:.75rem;color:var(--gray700);margin-top:4px">{{ $req->reason }}</div>
                </div>
                <div style="display:flex;gap:6px">
                  <form action="{{ route('admin.account-requests.approve', $req) }}" method="POST">@csrf<button class="modal-btn primary" style="padding:6px 14px;font-size:.74rem"><i class="fa-solid fa-check"></i> Approve</button></form>
                  <form action="{{ route('admin.account-requests.reject', $req) }}" method="POST">@csrf<button class="modal-btn danger" style="padding:6px 14px;font-size:.74rem"><i class="fa-solid fa-xmark"></i> Reject</button></form>
                </div>
              </div>
            </div>
            @endforeach
          </div>
          @endif

          <!-- Service Request History -->
          <div class="profile-card">
            <div class="profile-card-hd"><i class="fa-solid fa-clock-rotate-left"></i> Service Request History</div>
            <div style="overflow-x:auto">
              <table style="border-radius:0;border:none">
                <thead><tr><th>REQUEST #</th><th>SERVICE</th><th>DETAILS</th><th>DATE</th><th>TOTAL</th><th>STATUS</th></tr></thead>
                <tbody>
                  @forelse($user->serviceRequests??[] as $r)
                  <tr>
                    <td style="font-family:monospace;font-weight:700;font-size:.74rem">{{ $r->request_number }}</td>
                    <td><span class="tag" style="background:{{ $r->service_type==='printing'?'var(--blue-bg)':($r->service_type==='photocopy'?'var(--orange-bg)':'var(--g100)') }};color:{{ $r->service_color }}"><i class="fa-solid {{ $r->service_icon }}"></i> {{ ucfirst($r->service_type) }}</span></td>
                    <td style="font-size:.72rem;color:var(--gray600)">
                      @if($r->service_type==='research'){{ $r->duration_minutes }} min
                      @else{{ $r->copies }}x · {{ strtoupper($r->paper_size) }}@endif
                    </td>
                    <td style="font-size:.71rem;color:var(--gray600)">{{ $r->created_at->format('M d, Y') }}</td>
                    <td style="font-weight:700;color:var(--g700);font-size:.76rem">₱{{ number_format($r->total_price,2) }}</td>
                    <td>@php $sc=['pending'=>'tag-pend','approved'=>'tag-appr','processing'=>'tag-res','completed'=>'tag-done','rejected'=>'tag-rej']@endphp<span class="tag {{ $sc[$r->status]??'' }}">{{ strtoupper($r->status) }}</span></td>
                  </tr>
                  @empty
                  <tr><td colspan="6" style="text-align:center;padding:20px;color:var(--gray400);font-size:.78rem">No service requests yet.</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<!-- REJECT MODAL (for show page) -->
<div class="modal-bg" id="rejectModal">
  <div class="modal-box">
    <div class="modal-hd"><h3>Reject Account</h3><button class="modal-close" onclick="closeModal('rejectModal')"><i class="fa-solid fa-xmark"></i></button></div>
    <form action="{{ route('admin.users.reject',$user) }}" method="POST">
      @csrf
      <div class="modal-body"><div class="fg"><div class="flabel">Reason</div><textarea name="reason" class="fc" rows="3" required style="resize:vertical"></textarea></div></div>
      <div class="modal-footer"><button type="button" class="modal-btn secondary" onclick="closeModal('rejectModal')">Cancel</button><button type="submit" class="modal-btn danger">Reject</button></div>
    </form>
  </div>
</div>
<div class="modal-bg" id="deactModal">
  <div class="modal-box">
    <div class="modal-hd"><h3>Deactivate Account</h3><button class="modal-close" onclick="closeModal('deactModal')"><i class="fa-solid fa-xmark"></i></button></div>
    <form action="{{ route('admin.users.deactivate',$user) }}" method="POST">
      @csrf
      <div class="modal-body"><div class="fg"><div class="flabel">Reason (optional)</div><textarea name="reason" class="fc" rows="2" style="resize:vertical"></textarea></div></div>
      <div class="modal-footer"><button type="button" class="modal-btn secondary" onclick="closeModal('deactModal')">Cancel</button><button type="submit" class="modal-btn danger">Deactivate</button></div>
    </form>
  </div>
</div>
@endsection
@push('scripts')
<script>
function openModal(id){document.getElementById(id).classList.add('open')}
function closeModal(id){document.getElementById(id).classList.remove('open')}
document.querySelectorAll('.modal-bg').forEach(m=>m.addEventListener('click',e=>{if(e.target===m)m.classList.remove('open')}));
</script>
@endpush