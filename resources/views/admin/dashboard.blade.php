@extends('layouts.app')
@section('title','Admin Dashboard | IT Services System')
@section('body-class','dash-page')
@section('content')

@php
$admin = session('admin');
$totalUsers    = \App\Models\User::count();
$totalAdmins   = \App\Models\Admin::count();
$totalRequests = \App\Models\ServiceRequest::count();
$pendingUsers  = \App\Models\User::where('status','pending')->count();
$pendingReqs   = \App\Models\ServiceRequest::where('status','pending')->count();
$todayReqs     = \App\Models\ServiceRequest::whereDate('created_at', today())->count();
$recentRequests = \App\Models\ServiceRequest::with('user')->latest()->take(8)->get();
$pendingAccReqs = \App\Models\AccountRequest::with('user')->where('status','pending')->latest()->take(5)->get();
@endphp

<div class="dash-wrap">

  {{-- SIDEBAR --}}
  @include('admin.partials.sidebar')

  {{-- MAIN --}}
  <main class="main">

    {{-- TOPBAR --}}
    @include('admin.partials.topbar', [
      'title' => $admin->role === 'super_admin' ? 'Super Admin Dashboard' : 'Admin Dashboard',
      'sub'   => now()->format('l, F j, Y'),
    ])

    <div class="content">

      {{-- SUCCESS / ERROR ALERTS --}}
      @if(session('success'))
        <div class="abox ok" style="margin-bottom:16px">
          <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
      @endif

      {{-- STAT CARDS --}}
      <div class="stat-grid">

        <div class="stat-card" style="border-color:var(--g200)">
          <div class="stat-ico" style="background:var(--g100);color:var(--g700)">
            <i class="fa-solid fa-crown"></i>
          </div>
          <div>
            <div class="stat-lbl">Total Admins</div>
            <div class="stat-val">{{ $totalAdmins }}</div>
          </div>
        </div>

        <div class="stat-card" style="border-color:var(--blue-bg)">
          <div class="stat-ico" style="background:var(--blue-bg);color:var(--blue)">
            <i class="fa-solid fa-users"></i>
          </div>
          <div>
            <div class="stat-lbl">Total Users</div>
            <div class="stat-val">{{ $totalUsers }}</div>
          </div>
        </div>

        <div class="stat-card" style="border-color:var(--g200)">
          <div class="stat-ico" style="background:var(--g100);color:var(--g600)">
            <i class="fa-solid fa-file-lines"></i>
          </div>
          <div>
            <div class="stat-lbl">Total Requests</div>
            <div class="stat-val">{{ $totalRequests }}</div>
          </div>
        </div>

        <div class="stat-card" style="border-color:var(--orange-bg)">
          <div class="stat-ico" style="background:var(--orange-bg);color:var(--orange)">
            <i class="fa-solid fa-hourglass-half"></i>
          </div>
          <div>
            <div class="stat-lbl">Pending Requests</div>
            <div class="stat-val">{{ $pendingReqs }}</div>
          </div>
        </div>

      </div>

      {{-- SECONDARY STATS --}}
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px">

        <div class="stat-card" style="border-color:var(--orange-bg)">
          <div class="stat-ico" style="background:var(--orange-bg);color:var(--orange)">
            <i class="fa-solid fa-user-clock"></i>
          </div>
          <div>
            <div class="stat-lbl">Pending Registrations</div>
            <div class="stat-val">{{ $pendingUsers }}</div>
          </div>
        </div>

        <div class="stat-card" style="border-color:var(--g200)">
          <div class="stat-ico" style="background:var(--g100);color:var(--g600)">
            <i class="fa-solid fa-calendar-day"></i>
          </div>
          <div>
            <div class="stat-lbl">Today's Requests</div>
            <div class="stat-val">{{ $todayReqs }}</div>
          </div>
        </div>

        <div class="stat-card" style="border-color:var(--blue-bg)">
          <div class="stat-ico" style="background:var(--blue-bg);color:var(--blue)">
            <i class="fa-solid fa-chart-line"></i>
          </div>
          <div>
            <div class="stat-lbl">Completed Today</div>
            <div class="stat-val">
              {{ \App\Models\ServiceRequest::where('status','completed')->whereDate('updated_at', today())->count() }}
            </div>
          </div>
        </div>

      </div>

      {{-- QUICK ACTIONS --}}
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:22px">

        <a href="{{ route('admin.service-requests.index', ['status' => 'pending']) }}" class="qa-card" style="border-color:var(--orange-bg)">
          <div class="qa-ico" style="color:var(--orange)">
            <i class="fa-solid fa-user-clock"></i>
          </div>
          <div class="qa-lbl" style="color:var(--orange)">Pending Approvals</div>
          <div style="font-size:.7rem;color:var(--gray400);margin-top:4px">
            {{ $pendingUsers }} account(s) awaiting review
          </div>
        </a>

        <a href="{{ route('admin.service-requests.index', ['status' => 'pending']) }}" class="qa-card" style="border-color:var(--blue-bg)">
          <div class="qa-ico" style="color:var(--blue)">
            <i class="fa-solid fa-list-check"></i>
          </div>
          <div class="qa-lbl" style="color:var(--blue)">Pending Requests</div>
          <div style="font-size:.7rem;color:var(--gray400);margin-top:4px">
            {{ $pendingReqs }} request(s) to process
          </div>
        </a>

        @if($admin->role === 'super_admin')
        <a href="{{ route('admin.admins.index') }}" class="qa-card" style="border-color:var(--g200)">
          <div class="qa-ico" style="color:var(--g600)">
            <i class="fa-solid fa-user-shield"></i>
          </div>
          <div class="qa-lbl" style="color:var(--g600)">Manage Admins</div>
          <div style="font-size:.7rem;color:var(--gray400);margin-top:4px">
            Add or remove administrator accounts
          </div>
        </a>
        @else
        <a href="{{ route('admin.users.index') }}" class="qa-card" style="border-color:var(--g200)">
          <div class="qa-ico" style="color:var(--g600)">
            <i class="fa-solid fa-users"></i>
          </div>
          <div class="qa-lbl" style="color:var(--g600)">Manage Users</div>
          <div style="font-size:.7rem;color:var(--gray400);margin-top:4px">
            View and manage all user accounts
          </div>
        </a>
        @endif

      </div>

      {{-- TWO-COLUMN BOTTOM SECTION --}}
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">

        {{-- RECENT SERVICE REQUESTS --}}
        <div>
          <div class="section-hd">
            <h3>
              <i class="fa-solid fa-clock-rotate-left" style="color:var(--g600)"></i>
              Recent Service Requests
            </h3>
            <a href="{{ route('admin.service-requests.index') }}">View All →</a>
          </div>
          <div class="tbl-wrap">
            <table>
              <thead>
                <tr>
                  <th>REQ #</th>
                  <th>USER</th>
                  <th>SERVICE</th>
                  <th>STATUS</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                @forelse($recentRequests as $r)
                <tr>
                  <td style="font-family:monospace;font-size:.72rem;font-weight:700">
                    {{ $r->request_number }}
                  </td>
                  <td>
                    <div style="display:flex;align-items:center;gap:7px">
                      <div class="sb-avatar" style="width:26px;height:26px;font-size:.62rem;flex-shrink:0">
                        @if($r->user->profile_picture)
                          <img src="{{ Storage::url($r->user->profile_picture) }}" alt="">
                        @else
                          {{ strtoupper(substr($r->user->first_name,0,1)) }}
                        @endif
                      </div>
                      <div style="font-size:.72rem;font-weight:600;line-height:1.2">
                        {{ $r->user->first_name }}<br>
                        <span style="font-size:.65rem;color:var(--gray400)">{{ $r->user->id_number }}</span>
                      </div>
                    </div>
                  </td>
                  <td>
                    <span class="tag"
                      style="background:{{ $r->service_type==='printing'?'var(--blue-bg)':($r->service_type==='photocopy'?'var(--orange-bg)':'var(--g100)') }};
                             color:{{ $r->service_color }}">
                      <i class="fa-solid {{ $r->service_icon }}"></i>
                      {{ ucfirst($r->service_type) }}
                    </span>
                  </td>
                  <td>
                    @php
                      $sc = [
                        'pending'    => 'tag-pend',
                        'approved'   => 'tag-appr',
                        'processing' => 'tag-res',
                        'completed'  => 'tag-done',
                        'rejected'   => 'tag-rej',
                        'cancelled'  => 'tag-arch',
                      ];
                    @endphp
                    <span class="tag {{ $sc[$r->status] ?? 'tag-arch' }}">
                      {{ strtoupper($r->status) }}
                    </span>
                  </td>
                  <td>
                    <a href="{{ route('admin.service-requests.show', $r) }}" class="act-btn act-view" title="View">
                      <i class="fa-solid fa-eye"></i>
                    </a>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="5" style="text-align:center;padding:24px;color:var(--gray400);font-size:.78rem">
                    <i class="fa-solid fa-inbox" style="display:block;font-size:1.3rem;margin-bottom:6px"></i>
                    No service requests yet.
                  </td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>

        {{-- PENDING ACCOUNT REQUESTS --}}
        <div>
          <div class="section-hd">
            <h3>
              <i class="fa-solid fa-bell" style="color:var(--orange)"></i>
              Pending Account Requests
            </h3>
            <a href="{{ route('admin.users.index') }}">View All →</a>
          </div>

          @if($pendingAccReqs->count())
            <div style="display:flex;flex-direction:column;gap:10px">
              @foreach($pendingAccReqs as $req)
              <div style="background:var(--white);border-radius:12px;border:1.5px solid var(--gray200);padding:14px 16px;box-shadow:var(--shadow-sm)">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px">
                  <div style="display:flex;align-items:center;gap:10px;flex:1;min-width:0">
                    <div class="sb-avatar" style="width:34px;height:34px;font-size:.75rem;flex-shrink:0">
                      @if($req->user->profile_picture)
                        <img src="{{ Storage::url($req->user->profile_picture) }}" alt="">
                      @else
                        {{ strtoupper(substr($req->user->first_name,0,1)) }}
                      @endif
                    </div>
                    <div style="min-width:0">
                      <div style="font-size:.78rem;font-weight:700;color:var(--gray800)">
                        {{ $req->user->full_name }}
                      </div>
                      <div style="font-size:.68rem;color:var(--gray400)">
                        {{ $req->user->id_number }} · {{ $req->created_at->diffForHumans() }}
                      </div>
                      <div style="margin-top:4px">
                        <span class="tag tag-pend" style="font-size:.62rem">
                          {{ strtoupper($req->type) }} REQUEST
                        </span>
                      </div>
                      <div style="font-size:.72rem;color:var(--gray600);margin-top:4px;line-height:1.4">
                        {{ Str::limit($req->reason, 60) }}
                      </div>
                    </div>
                  </div>
                  <div style="display:flex;flex-direction:column;gap:5px;flex-shrink:0">
                    <form action="{{ route('admin.account-requests.approve', $req) }}" method="POST">
                      @csrf
                      <button type="submit" class="act-btn act-appr" title="Approve" style="width:auto;padding:0 10px;font-size:.68rem;font-weight:700;border-radius:6px;height:26px">
                        <i class="fa-solid fa-check"></i> Approve
                      </button>
                    </form>
                    <a href="{{ route('admin.users.show', $req->user) }}"
                       class="act-btn act-view" title="View User"
                       style="width:auto;padding:0 10px;font-size:.68rem;font-weight:700;border-radius:6px;height:26px;text-decoration:none;display:flex;align-items:center;gap:4px">
                      <i class="fa-solid fa-eye"></i> View
                    </a>
                  </div>
                </div>
              </div>
              @endforeach
            </div>
          @else
            <div style="background:var(--white);border-radius:12px;border:1.5px solid var(--gray200);padding:28px;text-align:center;color:var(--gray400);font-size:.78rem;box-shadow:var(--shadow-sm)">
              <i class="fa-solid fa-circle-check" style="display:block;font-size:1.5rem;margin-bottom:8px;color:var(--g400)"></i>
              No pending account requests.
            </div>
          @endif
        </div>

      </div>

      {{-- PENDING USER REGISTRATIONS --}}
      @if($pendingUsers > 0)
      <div style="margin-top:16px">
        <div class="section-hd">
          <h3>
            <i class="fa-solid fa-user-plus" style="color:var(--g600)"></i>
            New Registrations Awaiting Approval
          </h3>
          <a href="{{ route('admin.users.index', ['status' => 'pending']) }}">View All →</a>
        </div>
        <div class="tbl-wrap">
          <table>
            <thead>
              <tr>
                <th>USER</th>
                <th>ID NUMBER</th>
                <th>TYPE</th>
                <th>CAMPUS</th>
                <th>REGISTERED</th>
                <th>ACTIONS</th>
              </tr>
            </thead>
            <tbody>
              @foreach(\App\Models\User::where('status','pending')->latest()->take(5)->get() as $u)
              <tr>
                <td>
                  <div style="display:flex;align-items:center;gap:9px">
                    <div class="sb-avatar" style="width:30px;height:30px;font-size:.68rem;flex-shrink:0">
                      @if($u->profile_picture)
                        <img src="{{ Storage::url($u->profile_picture) }}" alt="">
                      @else
                        {{ strtoupper(substr($u->first_name,0,1)) }}
                      @endif
                    </div>
                    <div>
                      <div style="font-size:.76rem;font-weight:700">{{ $u->full_name }}</div>
                      <div style="font-size:.65rem;color:var(--gray400)">{{ $u->email }}</div>
                    </div>
                  </div>
                </td>
                <td style="font-family:monospace;font-size:.75rem">{{ $u->id_number }}</td>
                <td>
                  <span class="tag {{ $u->user_type === 'student' ? 'tag-student' : 'tag-faculty' }}">
                    {{ ucfirst(str_replace('_',' ',$u->user_type)) }}
                  </span>
                </td>
                <td style="font-size:.73rem">{{ config('campuses.'.$u->campus) }}</td>
                <td style="font-size:.71rem;color:var(--gray600)">{{ $u->created_at->diffForHumans() }}</td>
                <td>
                  <div style="display:flex;gap:5px">
                    <form action="{{ route('admin.users.approve', $u) }}" method="POST" style="display:inline">
                      @csrf
                      <button type="submit" class="act-btn act-appr" title="Approve">
                        <i class="fa-solid fa-check"></i>
                      </button>
                    </form>
                    <a href="{{ route('admin.users.show', $u) }}" class="act-btn act-view" title="View">
                      <i class="fa-solid fa-eye"></i>
                    </a>
                  </div>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
      @endif

    </div>{{-- end .content --}}
  </main>
</div>

{{-- FLOATING SUPPORT BUTTON --}}
<div style="position:fixed;bottom:24px;right:24px;z-index:100">
  <button style="width:48px;height:48px;border-radius:50%;background:linear-gradient(135deg,var(--g700),var(--g500));color:#fff;border:none;font-size:1rem;cursor:pointer;box-shadow:var(--shadow-md);transition:transform .2s"
          onmouseover="this.style.transform='scale(1.1)'"
          onmouseout="this.style.transform='scale(1)'"
          title="Support">
    <i class="fa-solid fa-headset"></i>
  </button>
</div>

@endsection