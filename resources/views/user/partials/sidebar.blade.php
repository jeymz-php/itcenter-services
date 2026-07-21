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
      @if($user->status === 'active')
      @php
        $sbPrintLimit = \App\Models\ServiceRequest::dailyPrintingLimit();
        $sbPrintUsed  = \App\Models\ServiceRequest::printingPagesUsedToday($user->id);
        $sbCopyLimit  = \App\Models\ServiceRequest::dailyPhotocopyLimit();
        $sbCopyUsed   = \App\Models\ServiceRequest::photocopyPagesUsedToday($user->id);
        $sbMinLimit   = \App\Models\ServiceRequest::dailyResearchLimit();
        $sbMinUsed    = \App\Models\ServiceRequest::minutesUsedToday($user->id);
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

{{-- USER MANUAL & GUIDE MODAL — lives in the sidebar partial so it's available
     on every authenticated page regardless of which one triggered it --}}
<div class="modal-bg" id="userGuideModal">
  <div class="modal-box" style="max-width:1100px;width:96vw;height:92vh;max-height:92vh">
    <div class="modal-hd">
      <h3>
        <img src="{{ asset('images/icons/user-guide_icon.png') }}" alt="" style="width:18px;height:18px;vertical-align:middle;margin-right:7px;object-fit:contain">
        User Manual &amp; Guide
      </h3>
      <button class="modal-close" onclick="closeModal('userGuideModal')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body" style="padding:0;flex:1;display:flex;flex-direction:column;min-height:0">
      <iframe src="{{ asset('documents/user-manual.pdf') }}#view=FitH" style="width:100%;height:100%;border:none;flex:1"></iframe>
    </div>
    <div class="modal-footer">
      <a href="{{ asset('documents/user-manual.pdf') }}" target="_blank" class="modal-btn secondary"
         style="text-decoration:none;display:inline-flex;align-items:center;gap:6px">
        <i class="fa-solid fa-up-right-from-square"></i> Open in New Tab
      </a>
      <button type="button" class="modal-btn primary" onclick="closeModal('userGuideModal')">Close</button>
    </div>
  </div>
</div>

@push('scripts')
<script>
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.modal-bg').forEach(m=>m.addEventListener('click',e=>{if(e.target===m)m.classList.remove('open')}));
</script>
@endpush