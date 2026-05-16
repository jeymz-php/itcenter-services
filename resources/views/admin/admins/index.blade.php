@extends('layouts.app')
@section('title','Manage Admins | Super Admin')
@section('body-class','dash-page')
@section('content')
@php $admin = session('admin'); @endphp

<!-- ADD ADMIN MODAL -->
<div class="modal-bg" id="addAdminModal">
  <div class="modal-box">
    <div class="modal-hd">
      <h3><i class="fa-solid fa-user-shield" style="color:var(--g600);margin-right:6px"></i>Add New Admin</h3>
      <button class="modal-close" onclick="closeModal('addAdminModal')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form action="{{ route('admin.admins.store') }}" method="POST">
      @csrf
      <div class="modal-body">
        <div class="fg"><div class="flabel">Admin ID</div><input type="text" name="admin_id" class="fc" placeholder="e.g. ADMIN002" required></div>
        <div class="fg"><div class="flabel">Email</div><input type="email" name="email" class="fc" placeholder="admin@ucc.edu.ph" required></div>
        <div class="g2">
          <div class="fg">
            <div class="flabel">Campus</div>
            <div class="sw"><select name="campus" class="fs" required><option value="" disabled selected>Select</option>@foreach(config('campuses') as $k=>$v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select></div>
          </div>
          <div class="fg">
            <div class="flabel">Role</div>
            <div class="sw"><select name="role" class="fs" required><option value="admin">Admin</option><option value="super_admin">Super Admin</option></select></div>
          </div>
        </div>
        <div class="fg"><div class="flabel">Password</div><input type="password" name="password" class="fc" placeholder="Min 8 characters" required></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="modal-btn secondary" onclick="closeModal('addAdminModal')">Cancel</button>
        <button type="submit" class="modal-btn primary"><i class="fa-solid fa-user-shield"></i> Add Admin</button>
      </div>
    </form>
  </div>
</div>

<div class="dash-wrap">
  @include('admin.partials.sidebar')
  <main class="main">
    @include('admin.partials.topbar', ['title' => 'Manage Admins', 'sub' => 'Administrator accounts (Super Admin only)'])
    <div class="content">

      @if(session('success'))
        <div class="abox ok" style="margin-bottom:14px"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
      @endif

      <div style="display:flex;justify-content:flex-end;margin-bottom:14px">
        <button class="filter-bar btn-sm" style="padding:9px 18px" onclick="openModal('addAdminModal')"><i class="fa-solid fa-user-shield"></i> Add New Admin</button>
      </div>

      <div class="tbl-wrap">
        <table>
          <thead>
            <tr><th>#</th><th>ADMIN ID</th><th>EMAIL</th><th>CAMPUS</th><th>ROLE</th><th>STATUS</th><th>CREATED</th><th>ACTIONS</th></tr>
          </thead>
          <tbody>
            @forelse($admins as $a)
            <tr>
              <td style="color:var(--gray400);font-size:.7rem">{{ $a->id }}</td>
              <td style="font-weight:700;font-family:monospace">{{ $a->admin_id }}</td>
              <td style="font-size:.74rem">{{ $a->email }}</td>
              <td style="font-size:.74rem">{{ config('campuses.'.$a->campus) }}</td>
              <td>
                <span class="tag {{ $a->role==='super_admin'?'':'tag-appr' }}" style="{{ $a->role==='super_admin'?'background:#fff8e1;color:#b86a00':'' }}">
                  {{ $a->role === 'super_admin' ? 'Super Admin' : 'Admin' }}
                </span>
              </td>
              <td>
                <span class="tag {{ ($a->status??'active')==='active'?'tag-active':'tag-deact' }}">
                  {{ strtoupper($a->status ?? 'ACTIVE') }}
                </span>
              </td>
              <td style="font-size:.72rem;color:var(--gray600)">{{ $a->created_at->format('M d, Y') }}</td>
              <td>
                <div style="display:flex;gap:4px">
                  @if($a->id !== $admin->id)
                  <form action="{{ route('admin.admins.toggle', $a) }}" method="POST" style="display:inline">@csrf
                    <button type="submit" class="act-btn {{ ($a->status??'active')==='active'?'act-deact':'act-actv' }}" title="Toggle Status">
                      <i class="fa-solid fa-{{ ($a->status??'active')==='active'?'user-slash':'user-check' }}"></i>
                    </button>
                  </form>
                  <form action="{{ route('admin.admins.destroy', $a) }}" method="POST" style="display:inline" onsubmit="return confirm('Delete admin {{ $a->admin_id }}?')">@csrf @method('DELETE')
                    <button type="submit" class="act-btn act-del" title="Delete"><i class="fa-solid fa-trash"></i></button>
                  </form>
                  @else
                  <span style="font-size:.7rem;color:var(--gray400)">Current</span>
                  @endif
                </div>
              </td>
            </tr>
            @empty
            <tr><td colspan="8" style="text-align:center;padding:28px;color:var(--gray400)">No admins found.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>
@endsection
@push('scripts')
<script>
function openModal(id){document.getElementById(id).classList.add('open')}
function closeModal(id){document.getElementById(id).classList.remove('open')}
document.querySelectorAll('.modal-bg').forEach(m=>m.addEventListener('click',e=>{if(e.target===m)m.classList.remove('open')}));
</script>
@endpush