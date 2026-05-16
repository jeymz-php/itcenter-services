@extends('layouts.app')
@section('title','Dashboard | IT Center Services')
@section('body-class','dash-page')
@section('content')
@php
  $user = Auth::user();
  $stats = [
    'pending'    => \App\Models\ServiceRequest::where('user_id',$user->id)->where('status','pending')->count(),
    'approved'   => \App\Models\ServiceRequest::where('user_id',$user->id)->where('status','approved')->count(),
    'processing' => \App\Models\ServiceRequest::where('user_id',$user->id)->where('status','processing')->count(),
    'completed'  => \App\Models\ServiceRequest::where('user_id',$user->id)->where('status','completed')->count(),
    'total'      => \App\Models\ServiceRequest::where('user_id',$user->id)->count(),
  ];
  $activeSession = \App\Models\ComputerSession::where('user_id',$user->id)
                   ->whereIn('status',['active','extended'])->with('computer','serviceRequest')->first();
  $recentRequests = \App\Models\ServiceRequest::where('user_id',$user->id)->latest()->take(5)->get();
@endphp

<div class="dash-wrap">
  @include('user.partials.sidebar')
  <main class="main">
    <div class="topbar">
      <div>
        <h1>
          @if($user->status==='pending') Account Pending Verification
          @elseif($user->status==='deactivated') Account Deactivated
          @elseif($user->status==='rejected') Account Rejected
          @else {{ ucfirst(str_replace('_',' ',$user->user_type)) }} Dashboard
          @endif
        </h1>
        <p>Welcome back, {{ $user->first_name }} {{ $user->last_name }}!</p>
      </div>
      <div class="topbar-right">
        <div class="clock">
          <i class="fa-solid fa-clock" style="color:var(--g600)"></i>
          <span id="clock">--:-- --</span>
        </div>
      </div>
    </div>

    <div class="content">

      @if(session('success'))
        <div class="abox ok" style="margin-bottom:16px">
          <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
      @endif

      {{-- ── PENDING STATE ── --}}
      @if($user->status === 'pending')
      <div class="verify-state">
        <div class="vi" style="background:var(--orange-bg);color:var(--orange)">
          <i class="fa-solid fa-hourglass-half"></i>
        </div>
        <h3>Your Account is Pending Verification</h3>
        <p>Your registration was successful. An IT Center administrator will review and verify your account shortly.</p>
        <div style="display:flex;gap:10px;flex-wrap:wrap;justify-content:center;margin-top:20px">
          <div style="background:var(--g100);border-radius:10px;padding:14px 20px;text-align:center;min-width:120px">
            <div style="font-size:1.3rem;margin-bottom:4px">📋</div>
            <div style="font-size:.73rem;font-weight:700;color:var(--g700)">Status</div>
            <div style="font-size:.76rem;color:var(--gray600)">Under Review</div>
          </div>
          <div style="background:var(--g100);border-radius:10px;padding:14px 20px;text-align:center;min-width:120px">
            <div style="font-size:1.3rem;margin-bottom:4px">⏱️</div>
            <div style="font-size:.73rem;font-weight:700;color:var(--g700)">Est. Time</div>
            <div style="font-size:.76rem;color:var(--gray600)">1–2 Business Days</div>
          </div>
          <div style="background:var(--g100);border-radius:10px;padding:14px 20px;text-align:center;min-width:120px">
            <div style="font-size:1.3rem;margin-bottom:4px">📧</div>
            <div style="font-size:.73rem;font-weight:700;color:var(--g700)">Notification</div>
            <div style="font-size:.76rem;color:var(--gray600)">Via Email</div>
          </div>
        </div>
        <div class="abox info" style="max-width:420px;margin-top:20px;text-align:left">
          <i class="fa-solid fa-circle-info"></i>
          <div>For faster verification, visit the IT Center with your valid UCC ID.<br>
          <strong>itcenter@ucc-caloocan.edu.ph</strong></div>
        </div>
      </div>

      {{-- ── DEACTIVATED STATE ── --}}
      @elseif($user->status === 'deactivated')
      <div class="verify-state">
        <div class="vi" style="background:#f3e5f5;color:#7b1fa2">
          <i class="fa-solid fa-user-slash"></i>
        </div>
        <h3>Your Account Has Been Deactivated</h3>
        <p>Your account is currently deactivated. You cannot access IT Center services at this time.</p>
        <a href="{{ route('profile') }}" class="btn" style="margin-top:20px;max-width:260px">
          <i class="fa-solid fa-rotate-left"></i> Request Reactivation
        </a>
      </div>

      {{-- ── REJECTED STATE ── --}}
      @elseif($user->status === 'rejected')
      <div class="verify-state">
        <div class="vi" style="background:var(--red-bg);color:var(--red)">
          <i class="fa-solid fa-user-xmark"></i>
        </div>
        <h3>Account Registration Rejected</h3>
        <p>Your account registration was not approved. Contact the IT Center for more information.</p>
        <div class="abox err" style="max-width:380px;margin-top:16px;text-align:left">
          <i class="fa-solid fa-envelope"></i>
          <div>Contact: <strong>itcenter@ucc-caloocan.edu.ph</strong></div>
        </div>
      </div>

      {{-- ── ACTIVE DASHBOARD ── --}}
      @else

      {{-- ACTIVE PC SESSION BANNER --}}
      @if($activeSession)
      <div style="background:linear-gradient(135deg,var(--g700),var(--g500));border-radius:14px;padding:18px 20px;margin-bottom:18px;color:#fff">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
          <div style="display:flex;align-items:center;gap:14px">
            <div style="width:48px;height:48px;border-radius:12px;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0">
              <i class="fa-solid fa-desktop"></i>
            </div>
            <div>
              <div style="font-size:.9rem;font-weight:800;margin-bottom:3px">
                Active PC Session — {{ $activeSession->computer->name }}
              </div>
              <div style="font-size:.75rem;opacity:.85">
                Request {{ $activeSession->serviceRequest->request_number }} ·
                Started {{ $activeSession->started_at->format('g:i A') }} ·
                Ends <strong id="session-ends">{{ $activeSession->ends_at->format('g:i A') }}</strong>
              </div>
            </div>
          </div>
          <div style="display:flex;align-items:center;gap:12px">
            <div style="text-align:center">
              <div id="session-countdown" style="font-size:1.6rem;font-weight:800;font-variant-numeric:tabular-nums;letter-spacing:-1px">--:--</div>
              <div style="font-size:.65rem;opacity:.7">remaining</div>
            </div>
            <button onclick="openModal('extendRequestModal')"
              style="background:rgba(255,255,255,.2);border:1.5px solid rgba(255,255,255,.4);color:#fff;border-radius:9px;padding:8px 16px;font-size:.78rem;font-weight:700;cursor:pointer;white-space:nowrap">
              <i class="fa-solid fa-clock"></i> Request Extension
            </button>
          </div>
        </div>
        {{-- Progress bar --}}
        <div style="background:rgba(255,255,255,.2);border-radius:6px;height:6px;margin-top:14px;overflow:hidden">
          <div id="session-progress" style="height:100%;border-radius:6px;background:rgba(255,255,255,.8);transition:width 1s linear;width:100%"></div>
        </div>
      </div>

      {{-- EXTEND SESSION REQUEST MODAL --}}
      <div class="modal-bg" id="extendRequestModal">
        <div class="modal-box">
          <div class="modal-hd">
            <h3><i class="fa-solid fa-clock" style="color:var(--g600);margin-right:6px"></i>Request Session Extension</h3>
            <button class="modal-close" onclick="closeModal('extendRequestModal')"><i class="fa-solid fa-xmark"></i></button>
          </div>
          <form action="{{ route('requests.request-extend', $activeSession->serviceRequest) }}" method="POST">
            @csrf
            <div class="modal-body">
              <div class="abox info" style="margin-bottom:14px">
                <i class="fa-solid fa-circle-info"></i>
                <div>Your extension request will be sent to the IT Center admin for approval. You will be notified once approved.</div>
              </div>
              <div class="fg">
                <div class="flabel">Extend By</div>
                <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:8px">
                  @foreach([15,30,45,60] as $min)
                  <label style="cursor:pointer">
                    <input type="radio" name="extend_minutes" value="{{ $min }}" style="display:none" required>
                    <div class="dur-opt" style="border:1.5px solid var(--gray200);border-radius:10px;padding:12px 6px;text-align:center;background:var(--white);transition:all .2s">
                      <div style="font-size:1.1rem;font-weight:800;color:var(--g700)">{{ $min }}</div>
                      <div style="font-size:.65rem;color:var(--gray400)">minutes</div>
                    </div>
                  </label>
                  @endforeach
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="modal-btn secondary" onclick="closeModal('extendRequestModal')">Cancel</button>
              <button type="submit" class="modal-btn primary"><i class="fa-solid fa-paper-plane"></i> Send Request</button>
            </div>
          </form>
        </div>
      </div>
      @endif

      {{-- STAT CARDS --}}
      <div class="stat-grid" style="grid-template-columns:repeat(5,1fr)">
        <div class="stat-card" style="border-color:var(--orange-bg)">
          <div class="stat-ico" style="background:var(--orange-bg);color:var(--orange)"><i class="fa-solid fa-hourglass-half"></i></div>
          <div><div class="stat-lbl">Pending</div><div class="stat-val">{{ $stats['pending'] }}</div></div>
        </div>
        <div class="stat-card" style="border-color:var(--blue-bg)">
          <div class="stat-ico" style="background:var(--blue-bg);color:var(--blue)"><i class="fa-solid fa-circle-check"></i></div>
          <div><div class="stat-lbl">Approved</div><div class="stat-val">{{ $stats['approved'] }}</div></div>
        </div>
        <div class="stat-card" style="border-color:var(--g100)">
          <div class="stat-ico" style="background:var(--g100);color:var(--g600)"><i class="fa-solid fa-gear"></i></div>
          <div><div class="stat-lbl">Processing</div><div class="stat-val">{{ $stats['processing'] }}</div></div>
        </div>
        <div class="stat-card" style="border-color:var(--g200)">
          <div class="stat-ico" style="background:var(--g100);color:var(--g700)"><i class="fa-solid fa-check-double"></i></div>
          <div><div class="stat-lbl">Completed</div><div class="stat-val">{{ $stats['completed'] }}</div></div>
        </div>
        <div class="stat-card" style="border-color:var(--purple-bg)">
          <div class="stat-ico" style="background:var(--purple-bg);color:var(--purple)"><i class="fa-solid fa-list-check"></i></div>
          <div><div class="stat-lbl">Total</div><div class="stat-val">{{ $stats['total'] }}</div></div>
        </div>
      </div>

      {{-- QUICK ACTIONS --}}
      <div class="qa-grid" style="margin-bottom:20px">
        <a href="{{ route('requests.printing') }}" class="qa-card" style="border-color:var(--blue-bg)">
          <div class="qa-ico" style="color:var(--blue)"><i class="fa-solid fa-print"></i></div>
          <div class="qa-lbl" style="color:var(--blue)">Printing</div>
        </a>
        <a href="{{ route('requests.photocopy') }}" class="qa-card" style="border-color:var(--orange-bg)">
          <div class="qa-ico" style="color:var(--orange)"><i class="fa-solid fa-copy"></i></div>
          <div class="qa-lbl" style="color:var(--orange)">Photocopy</div>
        </a>
        <a href="{{ route('requests.research') }}" class="qa-card" style="border-color:var(--g100)">
          <div class="qa-ico" style="color:var(--g600)"><i class="fa-solid fa-desktop"></i></div>
          <div class="qa-lbl" style="color:var(--g600)">Research</div>
        </a>
        <a href="{{ route('requests.history') }}" class="qa-card" style="border-color:var(--orange-bg)">
          <div class="qa-ico" style="color:#b86a00"><i class="fa-solid fa-clock-rotate-left"></i></div>
          <div class="qa-lbl" style="color:#b86a00">My Requests</div>
        </a>
      </div>

      {{-- RECENT REQUESTS --}}
      <div class="section-hd">
        <h3><i class="fa-solid fa-rectangle-list" style="color:var(--g600)"></i> Recent Requests</h3>
        <a href="{{ route('requests.history') }}">View All →</a>
      </div>
      <div class="tbl-wrap">
        <table>
          <thead>
            <tr><th>REQUEST #</th><th>SERVICE</th><th>DETAILS</th><th>DATE</th><th>STATUS</th></tr>
          </thead>
          <tbody>
            @forelse($recentRequests as $r)
            <tr>
              <td style="font-family:monospace;font-weight:700;font-size:.75rem">{{ $r->request_number }}</td>
              <td>
                @php
                  $bg = $r->service_type==='printing'?'var(--blue-bg)':($r->service_type==='photocopy'?'var(--orange-bg)':'var(--g100)');
                  $cl = $r->service_type==='printing'?'var(--blue)':($r->service_type==='photocopy'?'var(--orange)':'var(--g600)');
                  $ic = $r->service_type==='printing'?'fa-print':($r->service_type==='photocopy'?'fa-copy':'fa-desktop');
                @endphp
                <span class="tag" style="background:{{ $bg }};color:{{ $cl }}">
                  <i class="fa-solid {{ $ic }}"></i> {{ ucfirst($r->service_type) }}
                </span>
              </td>
              <td style="font-size:.74rem;color:var(--gray600)">
                @if($r->service_type==='printing') {{ $r->copies }}x · {{ strtoupper($r->paper_size) }}
                @elseif($r->service_type==='photocopy') {{ $r->copies }}x · {{ strtoupper($r->paper_size) }}
                @else {{ $r->duration_minutes }} min PC use
                @endif
              </td>
              <td style="font-size:.72rem;color:var(--gray600)">{{ $r->created_at->format('M d, Y') }}</td>
              <td>
                @php $sc=['pending'=>'tag-pend','approved'=>'tag-appr','processing'=>'tag-res','completed'=>'tag-done','rejected'=>'tag-rej'] @endphp
                <span class="tag {{ $sc[$r->status]??'tag-arch' }}">{{ strtoupper($r->status) }}</span>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="5" style="text-align:center;padding:28px;color:var(--gray400)">
                <i class="fa-solid fa-inbox" style="display:block;font-size:1.5rem;margin-bottom:8px"></i>
                No requests yet.
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      @endif {{-- end active --}}
    </div>
  </main>
</div>

@push('styles')
<style>
.stat-grid{display:grid;gap:12px;margin-bottom:18px}
input[type=radio]:checked+.dur-opt{border-color:var(--g500)!important;background:var(--g100)!important}
</style>
@endpush

@push('scripts')
<script>
function openModal(id){document.getElementById(id).classList.add('open')}
function closeModal(id){document.getElementById(id).classList.remove('open')}
document.querySelectorAll('.modal-bg').forEach(m=>m.addEventListener('click',e=>{if(e.target===m)m.classList.remove('open')}));

@if(isset($activeSession) && $activeSession)
const TOTAL_SEC = {{ $activeSession->total_minutes * 60 }};
let remaining   = {{ $activeSession->remaining_seconds }};
let warnPlayed  = false, alarmPlayed = false;

function playBeep(f=880,d=0.3){
  try{const c=new(window.AudioContext||window.webkitAudioContext)();const o=c.createOscillator();const g=c.createGain();o.connect(g);g.connect(c.destination);o.type='sine';o.frequency.value=f;g.gain.setValueAtTime(0.4,c.currentTime);g.gain.exponentialRampToValueAtTime(0.001,c.currentTime+d);o.start(c.currentTime);o.stop(c.currentTime+d);}catch(e){}
}

function fmt(s){const m=Math.floor(s/60),sec=s%60;return String(m).padStart(2,'0')+':'+String(sec).padStart(2,'0')}

function tickSession(){
  if(remaining<0)remaining=0;
  const el=document.getElementById('session-countdown');
  if(el)el.textContent=fmt(remaining);
  const pct=TOTAL_SEC>0?(remaining/TOTAL_SEC*100):0;
  const bar=document.getElementById('session-progress');
  if(bar){
    bar.style.width=pct+'%';
    if(remaining<=60)bar.style.background='rgba(229,62,62,.9)';
    else if(remaining<=300)bar.style.background='rgba(255,255,255,.6)';
  }
  if(el){
    if(remaining<=60)el.style.color='#ffcccc';
    else if(remaining<=300)el.style.color='#fff3cd';
    else el.style.color='#fff';
  }
  if(remaining<=300&&!warnPlayed){warnPlayed=true;playBeep(660,0.25);setTimeout(()=>playBeep(660,0.25),300);}
  if(remaining<=0&&!alarmPlayed){
    alarmPlayed=true;
    playBeep(880,0.3);setTimeout(()=>playBeep(660,0.3),350);setTimeout(()=>playBeep(440,0.6),700);
  }
  if(remaining>0){remaining--;setTimeout(tickSession,1000);}
}
tickSession();
@endif

(function tick(){
  const n=new Date(),h=n.getHours(),m=n.getMinutes(),s=n.getSeconds();
  const ap=h>=12?'PM':'AM',h12=h%12||12;
  const el=document.getElementById('clock');
  if(el)el.textContent=String(h12).padStart(2,'0')+':'+String(m).padStart(2,'0')+':'+String(s).padStart(2,'0')+' '+ap;
  setTimeout(tick,1000);
})();
</script>
@endpush
@endsection