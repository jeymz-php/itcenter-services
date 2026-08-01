@php
  $user = Auth::user();
  $serviceAvailability = $serviceAvailability ?? \App\Models\Setting::serviceAvailability();
@endphp
<aside class="sidebar">
  <div class="sb-brand">
    <button class="sb-toggle" onclick="toggleNav()" id="sb-toggle">
      <i class="fa-solid fa-bars" id="sb-icon"></i>
    </button>
    <img src="{{ asset('images/UCC_Logo.png') }}" alt="UCC">
    <div>
      <div class="sbn">IT Center Services</div>
      <div class="sbv" onclick="openModal('versionModal')" style="cursor:pointer" title="View what's new in this version">v{{ \App\Models\Setting::get('system_version', '1.0.0') }}</div>
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
      @if($user->status === 'active')
      @php
        $sbPrintLimit = $printingLimit ?? \App\Models\ServiceRequest::dailyPrintingLimit();
        $sbPrintUsed  = $printingUsedToday ?? \App\Models\ServiceRequest::printingPagesUsedToday($user->id);
        $sbCopyLimit  = $photocopyLimit ?? \App\Models\ServiceRequest::dailyPhotocopyLimit();
        $sbCopyUsed   = $photocopyUsedToday ?? \App\Models\ServiceRequest::photocopyPagesUsedToday($user->id);
        $sbMinLimit   = $minutesLimit ?? \App\Models\ServiceRequest::dailyResearchLimit();
        $sbMinUsed    = $minutesUsedToday ?? \App\Models\ServiceRequest::minutesUsedToday($user->id);
      @endphp
      <div style="margin-top:6px;font-size:.6rem;color:rgba(255,255,255,.55);display:flex;gap:7px;flex-wrap:wrap" title="Today's usage — resets 12:00 AM">
        <span><i class="fa-solid fa-print"></i> {{ $sbPrintUsed }}/{{ $sbPrintLimit }}</span>
        <span><i class="fa-solid fa-copy"></i> {{ $sbCopyUsed }}/{{ $sbCopyLimit }}</span>
        <span><i class="fa-solid fa-desktop"></i> {{ $sbMinUsed }}/{{ $sbMinLimit }}m</span>
      </div>
      @endif
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
       class="sb-link {{ request()->routeIs('requests.printing*') ? 'active' : '' }}"
       style="{{ !($serviceAvailability['printing'] ?? true) ? 'opacity:.58' : '' }}"
       title="{{ !($serviceAvailability['printing'] ?? true) ? 'Printing is currently unavailable' : 'Printing service' }}">
      <i class="fa-solid fa-print"></i> Printing
      @if(!($serviceAvailability['printing'] ?? true))<span class="nb" style="background:var(--red)">OFF</span>@endif
    </a>
    <a href="{{ route('requests.photocopy') }}"
       class="sb-link {{ request()->routeIs('requests.photocopy*') ? 'active' : '' }}"
       style="{{ !($serviceAvailability['photocopy'] ?? true) ? 'opacity:.58' : '' }}"
       title="{{ !($serviceAvailability['photocopy'] ?? true) ? 'Photocopy is currently unavailable' : 'Photocopy service' }}">
      <i class="fa-solid fa-copy"></i> Photocopy
      @if(!($serviceAvailability['photocopy'] ?? true))<span class="nb" style="background:var(--red)">OFF</span>@endif
    </a>
    <a href="{{ route('requests.research') }}"
       class="sb-link {{ request()->routeIs('requests.research*') ? 'active' : '' }}"
       style="{{ !($serviceAvailability['research'] ?? true) ? 'opacity:.58' : '' }}"
       title="{{ !($serviceAvailability['research'] ?? true) ? 'Research / PC Lab is currently unavailable' : 'Research / PC Lab service' }}">
      <i class="fa-solid fa-desktop"></i> Research / PC Lab
      @if(!($serviceAvailability['research'] ?? true))<span class="nb" style="background:var(--red)">OFF</span>@endif
    </a>

    <div class="sb-section">History</div>
    <a href="{{ route('requests.history') }}"
       class="sb-link {{ request()->routeIs('requests.history') ? 'active' : '' }}">
      <i class="fa-solid fa-clock-rotate-left"></i> My Requests
      @php $pendingCount = $stats['pending'] ?? \App\Models\ServiceRequest::where('user_id',$user->id)->where('status','pending')->count(); @endphp
      @if($pendingCount)<span class="nb">{{ $pendingCount }}</span>@endif
    </a>
    @endif

    <div class="sb-section">Support</div>
    <a href="{{ route('user.messages.index') }}"
       class="sb-link {{ request()->routeIs('user.messages.*') ? 'active' : '' }}">
      <i class="fa-solid fa-comments"></i> Messages
      @php $unreadMsgs = \App\Models\Message::where('user_id',$user->id)->where('sender_type','admin')->where('is_read_by_user',false)->count(); @endphp
      @if($unreadMsgs)<span class="nb" id="sb-msg-badge">{{ $unreadMsgs }}</span>@endif
    </a>
    <a href="#" onclick="openModal('userGuideModal');return false;" class="sb-link">
      <img src="{{ asset('images/icons/user-guide_icon.png') }}" alt="" style="width:14px;height:14px;object-fit:contain;filter:brightness(0) invert(1);opacity:.85">
      User Guide
    </a>
    <a href="#" onclick="openModal('versionModal');return false;" class="sb-link">
      <i class="fa-solid fa-rocket"></i> Version &amp; Updates
      <span class="nb" style="background:var(--g500)">v{{ \App\Models\Setting::get('system_version', '1.0.0') }}</span>
    </a>

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

    {{-- Logout lives inside .sb-nav on purpose: on mobile .sb-nav is hidden
         behind the hamburger toggle, so Logout hides/shows together with
         the rest of the nav instead of leaking out as its own row next to
         the logo (that was the "double header" bug on mobile). --}}
    <div style="padding:10px 10px 0">
      <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit" class="sb-link"
          style="background:rgba(229,62,62,.12);color:#ff8080;border-radius:8px;width:100%;border:none;cursor:pointer">
          <i class="fa-solid fa-right-from-bracket"></i> Logout
        </button>
      </form>
    </div>
  </nav>

  <div class="sb-footer">
    IT Services System<br>{{ config('campuses.'.$user->campus) }}
  </div>
</aside>

@include('partials.user-guide-modal')
@include('partials.version-modal')
