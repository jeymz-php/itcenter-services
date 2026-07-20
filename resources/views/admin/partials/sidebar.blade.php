@php
  $admin       = session('admin');
  $isSuper     = $admin->role === 'super_admin';
  $unread      = \App\Models\AdminNotification::where('is_read',false)->count();
  $pendingUsers = \App\Models\User::where('status','pending')
                    ->when(!$isSuper, fn($q) => $q->where('campus', $admin->campus))->count();
  $pendingReqs  = \App\Models\ServiceRequest::where('status','pending')
                    ->when(!$isSuper, fn($q) => $q->whereHas('user', fn($u) => $u->where('campus', $admin->campus)))->count();
  $pendingAccReqs = \App\Models\AccountRequest::where('status','pending')
                    ->when(!$isSuper, fn($q) => $q->whereHas('user', fn($u) => $u->where('campus', $admin->campus)))->count();
  $activeSessions = \App\Models\ComputerSession::whereIn('status',['active','extended'])
                    ->when(!$isSuper, fn($q) => $q->whereHas('user', fn($u) => $u->where('campus', $admin->campus)))->count();
@endphp

@php $pendingGuest = \App\Models\GuestRequest::where('status','pending')->when(!$isSuper, fn($q) => $q->where('campus', $admin->campus))->count(); @endphp

<aside class="sidebar">
  <div class="sb-brand">
    <button class="sb-toggle" onclick="toggleNav()" id="sb-toggle">
      <i class="fa-solid fa-bars" id="sb-icon"></i>
    </button>
    <img src="{{ asset('images/UCC_Logo.png') }}" alt="UCC">
    <div>
      <div class="sbn">IT Center Services</div>
      <div class="sbv">{{ $admin->role === 'super_admin' ? 'Super Admin Portal' : 'Admin Portal' }}</div>
    </div>
  </div>

  @push('scripts')
    <script>
      function toggleNav(){
        const nav=document.querySelector('.sb-nav');
          const icon=document.getElementById('sb-icon');
          nav.classList.toggle('mobile-open');
        icon.className=nav.classList.contains('mobile-open')?'fa-solid fa-xmark':'fa-solid fa-bars';
      }
    </script>
  @endpush

  <div class="sb-user">
    <div class="sb-avatar" style="background:linear-gradient(135deg,#f5a623,#e67e00)">
      <i class="fa-solid fa-{{ $admin->role==='super_admin'?'crown':'user-shield' }}" style="font-size:.8rem"></i>
    </div>
    <div>
      <div class="sb-uname">{{ $admin->admin_id }}</div>
      <div class="sb-uid" style="font-size:.62rem">{{ $admin->email }}</div>
      <span class="sb-badge" style="background:rgba(245,166,35,.2);color:#f5c842">
        {{ strtoupper(str_replace('_',' ',$admin->role)) }}
      </span>
    </div>
  </div>

  <nav class="sb-nav">

    <div class="sb-section">Main</div>
    <a href="{{ route('admin.dashboard') }}"
       class="sb-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
      <i class="fa-solid fa-gauge"></i> Dashboard
    </a>

    <div class="sb-section">Service Requests</div>
    <a href="{{ route('admin.service-requests.index') }}"
       class="sb-link {{ request()->routeIs('admin.service-requests.*') ? 'active' : '' }}">
      <i class="fa-solid fa-list-check"></i> Manage Requests
      @if($pendingReqs)<span class="nb">{{ $pendingReqs }}</span>@endif
    </a>
    <a href="{{ route('admin.service-requests.index', ['service_type'=>'printing']) }}"
       class="sb-link {{ request()->routeIs('admin.service-requests.*') && request('service_type')==='printing' ? 'active' : '' }}"
       style="padding-left:32px;font-size:.74rem">
      <i class="fa-solid fa-print"></i> Printing
    </a>
    <a href="{{ route('admin.service-requests.index', ['service_type'=>'photocopy']) }}"
       class="sb-link {{ request()->routeIs('admin.service-requests.*') && request('service_type')==='photocopy' ? 'active' : '' }}"
       style="padding-left:32px;font-size:.74rem">
      <i class="fa-solid fa-copy"></i> Photocopy
    </a>
    <a href="{{ route('admin.service-requests.index', ['service_type'=>'research']) }}"
       class="sb-link {{ request()->routeIs('admin.service-requests.*') && request('service_type')==='research' ? 'active' : '' }}"
       style="padding-left:32px;font-size:.74rem">
      <i class="fa-solid fa-desktop"></i> Research
      @if($activeSessions)<span class="nb" style="background:var(--g400)">{{ $activeSessions }}</span>@endif
    </a>
    <a href="{{ route('admin.guest-requests.index') }}"
        class="sb-link {{ request()->routeIs('admin.guest-requests.*') ? 'active' : '' }}">
        <i class="fa-solid fa-user-tag"></i> Guest Requests
        @php $pendingGuest = \App\Models\GuestRequest::where('status','pending')->count(); @endphp
        @if($pendingGuest)<span class="nb">{{ $pendingGuest }}</span>@endif
    </a>

    <div class="sb-section">User Management</div>
    <a href="{{ route('admin.users.index') }}"
       class="sb-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
      <i class="fa-solid fa-users"></i> Manage Users
      @if($pendingUsers)<span class="nb">{{ $pendingUsers }}</span>@endif
    </a>
    @if($admin->role === 'super_admin')
    <a href="{{ route('admin.admins.index') }}"
       class="sb-link {{ request()->routeIs('admin.admins.*') ? 'active' : '' }}">
      <i class="fa-solid fa-user-shield"></i> Manage Admins
    </a>
    <a href="{{ route('admin.settings.index') }}"
       class="sb-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
      <i class="fa-solid fa-sliders"></i> Settings
    </a>
    @endif
    @if($pendingAccReqs)
    <a href="{{ route('admin.users.index') }}"
       class="sb-link {{ request()->routeIs('admin.account-requests.*') ? 'active' : '' }}">
      <i class="fa-solid fa-user-clock"></i> Account Requests
      <span class="nb">{{ $pendingAccReqs }}</span>
    </a>
    @endif

    <div class="sb-section">Infrastructure</div>
    <a href="{{ route('admin.computers.index') }}"
       class="sb-link {{ request()->routeIs('admin.computers.*') ? 'active' : '' }}">
      <i class="fa-solid fa-computer"></i> Computers
      @if($activeSessions)<span class="nb" style="background:var(--g400)">{{ $activeSessions }} active</span>@endif
    </a>
    <a href="{{ route('admin.inventory.index') }}"
       class="sb-link {{ request()->routeIs('admin.inventory.*') ? 'active' : '' }}">
      <i class="fa-solid fa-boxes-stacked"></i> Inventory
    </a>

    <div class="sb-section">Communication</div>
    <a href="{{ route('admin.messages.index') }}"
       class="sb-link {{ request()->routeIs('admin.messages.*') ? 'active' : '' }}">
      <i class="fa-solid fa-comments"></i> Messages
      @php $unreadMsgs = \App\Models\Message::unreadByAdmin()->pluck('user_id')->unique()->count(); @endphp
      @if($unreadMsgs)<span class="nb" id="sb-msg-badge">{{ $unreadMsgs }}</span>@endif
    </a>

    <div class="sb-section">Reports & Analytics</div>
    <a href="{{ route('admin.reports.index') }}"
       class="sb-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
      <i class="fa-solid fa-chart-line"></i> Reports
    </a>
    <a href="#" class="sb-link"><i class="fa-solid fa-star"></i> Review Ratings</a>
    <a href="{{ route('admin.notifications') }}"
       class="sb-link {{ request()->routeIs('admin.notifications') ? 'active' : '' }}">
      <i class="fa-solid fa-bell"></i> Notifications
      @if($unread)<span class="nb">{{ $unread }}</span>@endif
    </a>

    {{-- Logout lives inside .sb-nav on purpose: on mobile .sb-nav is hidden
         behind the hamburger toggle, so Logout hides/shows together with
         the rest of the nav instead of leaking out as its own row next to
         the logo (that was the "double header" bug on mobile). --}}
    <div style="padding:10px 10px 0">
      <form action="{{ route('admin.logout') }}" method="POST">
        @csrf
        <button type="submit" class="sb-link"
          style="background:rgba(229,62,62,.12);color:#ff8080;border-radius:8px;width:100%;border:none;cursor:pointer">
          <i class="fa-solid fa-right-from-bracket"></i> Logout
        </button>
      </form>
    </div>

  </nav>

  <div class="sb-footer">
    IT Services System<br>
    {{ config('campuses.'.$admin->campus, 'All Campuses') }}
  </div>
</aside>