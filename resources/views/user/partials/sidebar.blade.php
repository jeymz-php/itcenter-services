@php $user = Auth::user(); @endphp
<aside class="sidebar">
  <div class="sb-brand">
    <button class="sb-toggle" onclick="toggleNav()" id="sb-toggle">
      <i class="fa-solid fa-bars" id="sb-icon"></i>
    </button>
    <img src="{{ asset('images/UCC_Logo.png') }}" alt="UCC">
    <div>
      <div class="sbn">IT Center Services</div>
      <div class="sbv">IT Center Services System v1.0</div>
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
    <div class="sb-avatar">
      @if($user->profile_picture)
        <img src="{{ Storage::url($user->profile_picture) }}" alt="">
      @else
        {{ strtoupper(substr($user->first_name,0,1)) }}
      @endif
    </div>
    <div>
      <div class="sb-uname">{{ $user->first_name }} {{ $user->last_name }}</div>
      <div class="sb-uid">ID: {{ $user->id_number }}</div>
      <div class="sb-uid" style="font-size:.6rem">{{ config('campuses.'.$user->campus) }}</div>
      <span class="sb-badge">{{ ucfirst(str_replace('_',' ',$user->user_type)) }}</span>
    </div>
  </div>

  <nav class="sb-nav">
    <div class="sb-section">Main</div>
    <a href="{{ route('dashboard') }}"
       class="sb-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
      <i class="fa-solid fa-gauge"></i> Dashboard
    </a>

    @if($user->status === 'active')
    <div class="sb-section">Services</div>
    <a href="{{ route('requests.printing') }}"
       class="sb-link {{ request()->routeIs('requests.printing*') ? 'active' : '' }}">
      <i class="fa-solid fa-print"></i> Printing
    </a>
    <a href="{{ route('requests.photocopy') }}"
       class="sb-link {{ request()->routeIs('requests.photocopy*') ? 'active' : '' }}">
      <i class="fa-solid fa-copy"></i> Photocopy
    </a>
    <a href="{{ route('requests.research') }}"
       class="sb-link {{ request()->routeIs('requests.research*') ? 'active' : '' }}">
      <i class="fa-solid fa-desktop"></i> Research / PC Lab
    </a>

    <div class="sb-section">History</div>
    <a href="{{ route('requests.history') }}"
       class="sb-link {{ request()->routeIs('requests.history') ? 'active' : '' }}">
      <i class="fa-solid fa-clock-rotate-left"></i> My Requests
      @php $pendingCount = \App\Models\ServiceRequest::where('user_id',$user->id)->where('status','pending')->count(); @endphp
      @if($pendingCount)<span class="nb">{{ $pendingCount }}</span>@endif
    </a>
    @endif

    <div class="sb-section">Account</div>
    <a href="{{ route('profile') }}"
       class="sb-link {{ request()->routeIs('profile') ? 'active' : '' }}">
      <i class="fa-solid fa-user"></i> My Profile
    </a>
    @if($user->status === 'deactivated')
    <a href="{{ route('profile') }}" class="sb-link" style="color:rgba(171,71,188,.8)">
      <i class="fa-solid fa-user-check"></i> Request Reactivation
    </a>
    @endif
  </nav>

  <div style="padding:0 10px 12px">
    <form action="{{ route('logout') }}" method="POST">
      @csrf
      <button type="submit" class="sb-link"
        style="background:rgba(229,62,62,.12);color:#ff8080;border-radius:8px;width:100%;border:none;cursor:pointer">
        <i class="fa-solid fa-right-from-bracket"></i> Logout
      </button>
    </form>
  </div>

  <div class="sb-footer">
    IT Services System<br>{{ config('campuses.'.$user->campus) }}
  </div>
</aside>