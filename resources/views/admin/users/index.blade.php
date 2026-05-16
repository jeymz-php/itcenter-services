@extends('layouts.app')
@section('title','Manage Users | Admin')
@section('body-class','dash-page')
@section('content')
@php $admin = session('admin'); @endphp

<!-- ADD USER MODAL -->
<div class="modal-bg" id="addUserModal">
  <div class="modal-box">
    <div class="modal-hd">
      <h3><i class="fa-solid fa-user-plus" style="color:var(--g600);margin-right:6px"></i>Add New User</h3>
      <button class="modal-close" onclick="closeModal('addUserModal')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form action="{{ route('admin.users.store') }}" method="POST">
      @csrf
      <div class="modal-body">
        <div class="g2">
          <div class="fg"><div class="flabel">First Name</div><input type="text" name="first_name" class="fc" placeholder="First name" required></div>
          <div class="fg"><div class="flabel">Last Name</div><input type="text" name="last_name" class="fc" placeholder="Last name" required></div>
        </div>
        <div class="fg"><div class="flabel">ID Number (8 digits)</div><input type="text" name="id_number" class="fc" maxlength="8" placeholder="e.g. 20220001" required></div>
        <div class="fg"><div class="flabel">Email</div><input type="email" name="email" class="fc" placeholder="Email address" required></div>
        <div class="g2">
          <div class="fg">
            <div class="flabel">Campus</div>
            <div class="sw"><select name="campus" class="fs" required><option value="" disabled selected>Select</option>@foreach(config('campuses') as $k=>$v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select></div>
          </div>
          <div class="fg">
            <div class="flabel">User Type</div>
            <div class="sw"><select name="user_type" class="fs" required><option value="" disabled selected>Select</option><option value="student">Student</option><option value="faculty_staff">Faculty / Staff</option></select></div>
          </div>
        </div>
        <div class="fg"><div class="flabel">Password</div><input type="password" name="password" class="fc" placeholder="Min 8 characters" required></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="modal-btn secondary" onclick="closeModal('addUserModal')">Cancel</button>
        <button type="submit" class="modal-btn primary"><i class="fa-solid fa-user-plus"></i> Add User</button>
      </div>
    </form>
  </div>
</div>

<!-- REJECT MODAL -->
<div class="modal-bg" id="rejectModal">
  <div class="modal-box">
    <div class="modal-hd">
      <h3><i class="fa-solid fa-user-xmark" style="color:var(--red);margin-right:6px"></i>Reject Account</h3>
      <button class="modal-close" onclick="closeModal('rejectModal')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form id="rejectForm" method="POST">
      @csrf
      <div class="modal-body">
        <p>Please provide a reason for rejecting this account:</p>
        <div class="fg"><textarea name="reason" class="fc" rows="3" placeholder="Reason for rejection..." required style="resize:vertical"></textarea></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="modal-btn secondary" onclick="closeModal('rejectModal')">Cancel</button>
        <button type="submit" class="modal-btn danger"><i class="fa-solid fa-xmark"></i> Reject</button>
      </div>
    </form>
  </div>
</div>

<!-- DEACTIVATE MODAL -->
<div class="modal-bg" id="deactModal">
  <div class="modal-box">
    <div class="modal-hd">
      <h3><i class="fa-solid fa-user-slash" style="color:#7b1fa2;margin-right:6px"></i>Deactivate Account</h3>
      <button class="modal-close" onclick="closeModal('deactModal')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form id="deactForm" method="POST">
      @csrf
      <div class="modal-body">
        <p>You are about to deactivate this account. The user will still be able to log in but will see a deactivated notice.</p>
        <div class="fg"><textarea name="reason" class="fc" rows="2" placeholder="Reason (optional)..." style="resize:vertical"></textarea></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="modal-btn secondary" onclick="closeModal('deactModal')">Cancel</button>
        <button type="submit" class="modal-btn danger"><i class="fa-solid fa-user-slash"></i> Deactivate</button>
      </div>
    </form>
  </div>
</div>

<div class="dash-wrap">
  @include('admin.partials.sidebar')
  <main class="main">
    @include('admin.partials.topbar', ['title' => 'Manage Users', 'sub' => 'Students, Faculty & Staff accounts'])
    <div class="content">

      @if(session('success'))
        <div class="abox ok" style="margin-bottom:14px"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
      @endif
      @if($errors->any())
        <div class="abox err" style="margin-bottom:14px"><i class="fa-solid fa-triangle-exclamation"></i> {{ $errors->first() }}</div>
      @endif

      <!-- STATUS TABS -->
      <div class="tab-pills">
        <a href="{{ route('admin.users.index') }}" class="tab-pill {{ !request('status') ? 'active' : '' }}">All <span class="cnt">{{ $counts['all'] }}</span></a>
        <a href="{{ route('admin.users.index', ['status'=>'pending']) }}" class="tab-pill {{ request('status')=='pending' ? 'active' : '' }}" style="{{ request('status')=='pending'?'':'border-color:#f5a623;color:#b86a00' }}">Pending <span class="cnt">{{ $counts['pending'] }}</span></a>
        <a href="{{ route('admin.users.index', ['status'=>'active']) }}" class="tab-pill {{ request('status')=='active' ? 'active' : '' }}">Active <span class="cnt">{{ $counts['active'] }}</span></a>
        <a href="{{ route('admin.users.index', ['status'=>'deactivated']) }}" class="tab-pill {{ request('status')=='deactivated' ? 'active' : '' }}">Deactivated <span class="cnt">{{ $counts['deactivated'] }}</span></a>
        <a href="{{ route('admin.users.index', ['status'=>'archived']) }}" class="tab-pill {{ request('status')=='archived' ? 'active' : '' }}">Archived <span class="cnt">{{ $counts['archived'] }}</span></a>
        <a href="{{ route('admin.users.index', ['status'=>'rejected']) }}" class="tab-pill {{ request('status')=='rejected' ? 'active' : '' }}">Rejected <span class="cnt">{{ $counts['rejected'] }}</span></a>
      </div>

      <!-- FILTER BAR -->
      <form class="filter-bar" method="GET" action="{{ route('admin.users.index') }}">
        <input type="hidden" name="status" value="{{ request('status') }}">
        <input type="text" name="search" class="fc" placeholder="🔍 Search name, ID, email..." value="{{ request('search') }}">
        <div class="sw" style="min-width:150px"><select name="user_type" class="fs"><option value="">All Types</option><option value="student" {{ request('user_type')=='student'?'selected':'' }}>Student</option><option value="faculty_staff" {{ request('user_type')=='faculty_staff'?'selected':'' }}>Faculty/Staff</option></select></div>
        <div class="sw" style="min-width:170px"><select name="campus" class="fs"><option value="">All Campuses</option>@foreach(config('campuses') as $k=>$v)<option value="{{ $k }}" {{ request('campus')==$k?'selected':'' }}>{{ $v }}</option>@endforeach</select></div>
        <button type="submit" class="btn-sm"><i class="fa-solid fa-magnifying-glass"></i> Filter</button>
        <a href="{{ route('admin.users.index') }}" class="btn-outline">Reset</a>
        <button type="button" class="btn-sm" onclick="openModal('addUserModal')" style="margin-left:auto"><i class="fa-solid fa-user-plus"></i> Add User</button>
      </form>

      <!-- TABLE -->
      <div class="tbl-wrap">
        <table>
          <thead>
            <tr><th>#</th><th>USER</th><th>ID NUMBER</th><th>TYPE</th><th>CAMPUS</th><th>STATUS</th><th>REGISTERED</th><th>ACTIONS</th></tr>
          </thead>
          <tbody>
            @forelse($users as $user)
            <tr>
              <td style="color:var(--gray400);font-size:.7rem">#{{ $user->id }}</td>
              <td>
                <div style="display:flex;align-items:center;gap:9px">
                  <div class="sb-avatar" style="width:32px;height:32px;font-size:.7rem;flex-shrink:0">
                    @if($user->profile_picture)<img src="{{ Storage::url($user->profile_picture) }}" alt="">@else{{ strtoupper(substr($user->first_name,0,1)) }}@endif
                  </div>
                  <div>
                    <div style="font-size:.78rem;font-weight:700">{{ $user->full_name }}</div>
                    <div style="font-size:.65rem;color:var(--gray400)">{{ $user->email }}</div>
                  </div>
                </div>
              </td>
              <td style="font-family:monospace;font-size:.76rem">{{ $user->id_number }}</td>
              <td><span class="tag {{ $user->user_type=='student'?'tag-student':'tag-faculty' }}">{{ ucfirst(str_replace('_',' ',$user->user_type)) }}</span></td>
              <td style="font-size:.74rem">{{ config('campuses.'.$user->campus) }}</td>
              <td>
                @php $sc=['pending'=>'tag-pend','active'=>'tag-active','deactivated'=>'tag-deact','archived'=>'tag-arch','rejected'=>'tag-rej'] @endphp
                <span class="tag {{ $sc[$user->status]??'tag-arch' }}">{{ strtoupper($user->status) }}</span>
              </td>
              <td style="font-size:.72rem;color:var(--gray600)">{{ $user->created_at->format('M d, Y') }}</td>
              <td>
                <div style="display:flex;gap:4px;flex-wrap:wrap">
                  <a href="{{ route('admin.users.show', $user) }}" class="act-btn act-view" title="View"><i class="fa-solid fa-eye"></i></a>

                  @if($user->status === 'pending')
                    <form action="{{ route('admin.users.approve', $user) }}" method="POST" style="display:inline">@csrf<button type="submit" class="act-btn act-appr" title="Approve"><i class="fa-solid fa-check"></i></button></form>
                    <button class="act-btn act-del" title="Reject" onclick="openReject('{{ route('admin.users.reject', $user) }}')"><i class="fa-solid fa-xmark"></i></button>
                  @endif

                  @if($user->status === 'active')
                    <button class="act-btn act-deact" title="Deactivate" onclick="openDeact('{{ route('admin.users.deactivate', $user) }}')"><i class="fa-solid fa-user-slash"></i></button>
                    <form action="{{ route('admin.users.archive', $user) }}" method="POST" style="display:inline">@csrf<button type="submit" class="act-btn act-arch" title="Archive"><i class="fa-solid fa-box-archive"></i></button></form>
                  @endif

                  @if($user->status === 'deactivated')
                    <form action="{{ route('admin.users.activate', $user) }}" method="POST" style="display:inline">@csrf<button type="submit" class="act-btn act-actv" title="Activate"><i class="fa-solid fa-user-check"></i></button></form>
                  @endif

                  @if($user->status === 'archived')
                    <form action="{{ route('admin.users.activate', $user) }}" method="POST" style="display:inline">@csrf<button type="submit" class="act-btn act-actv" title="Restore"><i class="fa-solid fa-rotate-left"></i></button></form>
                  @endif

                  <form action="{{ route('admin.users.destroy', $user) }}" method="POST" style="display:inline" onsubmit="return confirm('Permanently delete {{ $user->full_name }}?')">@csrf @method('DELETE')<button type="submit" class="act-btn act-del" title="Delete"><i class="fa-solid fa-trash"></i></button></form>
                </div>
              </td>
            </tr>
            @empty
            <tr><td colspan="8" style="text-align:center;padding:28px;color:var(--gray400)"><i class="fa-solid fa-users" style="display:block;font-size:1.5rem;margin-bottom:8px"></i>No users found.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <!-- PAGINATION -->
      <div style="margin-top:14px">{{ $users->links() }}</div>

    </div>
  </main>
</div>
@endsection
@push('scripts')
<script>
function openModal(id){document.getElementById(id).classList.add('open')}
function closeModal(id){document.getElementById(id).classList.remove('open')}
document.querySelectorAll('.modal-bg').forEach(m=>m.addEventListener('click',e=>{if(e.target===m)m.classList.remove('open')}));
function openReject(url){
  document.getElementById('rejectForm').action=url+'?_method=POST';
  document.getElementById('rejectForm').setAttribute('action',url);
  openModal('rejectModal');
}
function openDeact(url){
  document.getElementById('deactForm').setAttribute('action',url);
  openModal('deactModal');
}
</script>
@endpush