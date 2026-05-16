@extends('user.requests._layout')
@section('title','Research Request | IT Center')
@section('page-title','Research / Computer Lab')
@section('page-sub','Request a PC slot for research or academic work')

@section('request-content')
@php
  $user = Auth::user();
  $activeSession = \App\Models\ComputerSession::where('user_id', $user->id)
                   ->whereIn('status',['active','extended'])
                   ->with('computer','serviceRequest')
                   ->first();
@endphp

{{-- EXTEND REQUEST MODAL --}}
@if($activeSession)
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
          <div>Your extension request will be sent to the admin for approval.</div>
        </div>
        <div class="fg">
          <div class="flabel">Extend By</div>
          <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:8px">
            @foreach([15,30,45,60] as $min)
            <label style="cursor:pointer">
              <input type="radio" name="extend_minutes" value="{{ $min }}" style="display:none" required>
              <div style="border:1.5px solid var(--gray200);border-radius:10px;padding:12px 6px;text-align:center;background:var(--white);transition:all .2s"
                   class="dur-radio-opt">
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

{{-- TERMS MODAL --}}
<div class="modal-bg" id="resTerms">
  <div class="modal-box">
    <div class="modal-hd">
      <h3><i class="fa-solid fa-desktop" style="color:var(--g600);margin-right:7px"></i>Computer Lab — Terms & Conditions</h3>
      <button class="modal-close" onclick="closeModal('resTerms')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body">
      <h4>1. Purpose of Use</h4>
      <p>Computer lab resources are for academic and research purposes only. Gaming and entertainment are prohibited.</p>
      <h4>2. Time Slots</h4>
      <p>Available in 15, 30, 45, or 60-minute increments. Extensions subject to availability.</p>
      <h4>3. Proper Use</h4>
      <p>Handle equipment with care. Do not install software or change system settings.</p>
      <h4>4. Internet Usage</h4>
      <p>Internet is for academic use only. Social media, streaming, or inappropriate content is prohibited.</p>
      <h4>5. No-show Policy</h4>
      <p>Approved requests not claimed within 10 minutes will be forfeited.</p>
    </div>
    <div class="modal-footer">
      <button class="modal-btn primary" onclick="acceptTerms('resTerms','terms_check')"><i class="fa-solid fa-check"></i> I Agree</button>
    </div>
  </div>
</div>

<div style="max-width:680px;margin:0 auto">

  @if(session('success'))
    <div class="abox ok" style="margin-bottom:16px"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
  @endif
  @if($errors->any())
    <div class="abox err" style="margin-bottom:16px">
      <i class="fa-solid fa-triangle-exclamation"></i>
      <div>@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
    </div>
  @endif

  {{-- ── ACTIVE SESSION VIEW ── --}}
  @if($activeSession)
  <div style="background:var(--white);border-radius:16px;box-shadow:var(--shadow-sm);border:1.5px solid var(--g300);overflow:hidden;margin-bottom:16px">

    {{-- Header --}}
    <div style="background:linear-gradient(135deg,var(--g700),var(--g500));padding:18px 22px;display:flex;align-items:center;gap:14px">
      <div style="width:46px;height:46px;border-radius:12px;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.2rem;flex-shrink:0">
        <i class="fa-solid fa-desktop"></i>
      </div>
      <div style="flex:1">
        <div style="font-size:1rem;font-weight:800;color:#fff">Active PC Session</div>
        <div style="font-size:.74rem;color:rgba(255,255,255,.75)">
          {{ $activeSession->computer->name }} — {{ $activeSession->serviceRequest->request_number }}
        </div>
      </div>
      <span style="background:rgba(255,255,255,.2);color:#fff;font-size:.7rem;font-weight:700;padding:4px 10px;border-radius:20px">
        {{ strtoupper($activeSession->status) }}
      </span>
    </div>

    <div style="padding:22px">

      {{-- Big Countdown --}}
      <div style="text-align:center;margin-bottom:20px">
        <div style="font-size:.76rem;color:var(--gray600);margin-bottom:4px">Time Remaining</div>
        <div id="countdown" style="font-size:4rem;font-weight:800;color:var(--g700);font-variant-numeric:tabular-nums;letter-spacing:-3px;line-height:1">
          --:--
        </div>
        <div style="font-size:.72rem;color:var(--gray400);margin-top:4px">
          Ends at <strong id="ends-at-display">{{ $activeSession->ends_at->format('g:i A') }}</strong>
        </div>
      </div>

      {{-- Progress Bar --}}
      <div style="background:var(--gray200);border-radius:8px;height:10px;overflow:hidden;margin-bottom:20px">
        <div id="progress-bar"
             style="height:100%;border-radius:8px;background:linear-gradient(90deg,var(--g500),var(--g300));transition:width 1s linear;width:100%">
        </div>
      </div>

      {{-- Session Info Grid --}}
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px">
        <div style="background:var(--g50);border-radius:10px;padding:12px;text-align:center;border:1.5px solid var(--g200)">
          <div style="font-size:.62rem;color:var(--gray400);font-weight:700;text-transform:uppercase;margin-bottom:4px">PC Assigned</div>
          <div style="font-size:.9rem;font-weight:800;color:var(--g700)"><i class="fa-solid fa-computer"></i> {{ $activeSession->computer->name }}</div>
        </div>
        <div style="background:var(--g50);border-radius:10px;padding:12px;text-align:center;border:1.5px solid var(--g200)">
          <div style="font-size:.62rem;color:var(--gray400);font-weight:700;text-transform:uppercase;margin-bottom:4px">Started At</div>
          <div style="font-size:.9rem;font-weight:800;color:var(--g700)">{{ $activeSession->started_at->format('g:i A') }}</div>
        </div>
        <div style="background:var(--g50);border-radius:10px;padding:12px;text-align:center;border:1.5px solid var(--g200)">
          <div style="font-size:.62rem;color:var(--gray400);font-weight:700;text-transform:uppercase;margin-bottom:4px">Duration</div>
          <div style="font-size:.9rem;font-weight:800;color:var(--g700)">
            {{ $activeSession->duration_minutes }}m
            @if($activeSession->extended_minutes)
              <span style="font-size:.7rem;color:var(--orange)">+{{ $activeSession->extended_minutes }}m</span>
            @endif
          </div>
        </div>
      </div>

      {{-- Action Buttons --}}
      <div style="display:flex;gap:10px">
        <button onclick="openModal('extendRequestModal')"
          style="flex:1;padding:12px;background:var(--orange-bg);color:var(--orange);border:1.5px solid var(--orange);border-radius:var(--rs);font-family:inherit;font-size:.82rem;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:7px;transition:all .2s"
          onmouseover="this.style.background='var(--orange)';this.style.color='#fff'"
          onmouseout="this.style.background='var(--orange-bg)';this.style.color='var(--orange)'">
          <i class="fa-solid fa-clock"></i> Request Extension
        </button>
        <div style="flex:1;padding:12px;background:var(--g100);color:var(--g700);border-radius:var(--rs);font-size:.78rem;text-align:center;border:1.5px solid var(--g200)">
          <i class="fa-solid fa-circle-info"></i><br>
          <span style="font-size:.7rem">Contact IT Center staff to end session early</span>
        </div>
      </div>

      {{-- Expired notice --}}
      <div id="expired-notice" style="display:none;margin-top:14px">
        <div class="abox err">
          <i class="fa-solid fa-clock"></i>
          <div><strong>Session time is up!</strong> Please log off the PC and notify IT Center staff.</div>
        </div>
      </div>

    </div>
  </div>

  {{-- After session ends, show new request option --}}
  <div style="background:var(--white);border-radius:14px;border:1.5px solid var(--gray200);padding:18px 22px;box-shadow:var(--shadow-sm)">
    <div style="font-size:.85rem;font-weight:700;color:var(--gray800);margin-bottom:4px">
      <i class="fa-solid fa-circle-info" style="color:var(--g600)"></i> Need more time?
    </div>
    <div style="font-size:.76rem;color:var(--gray600);margin-bottom:12px">
      Use the "Request Extension" button above, or submit a new research request after your current session ends.
    </div>
    <a href="{{ route('dashboard') }}"
       style="font-size:.76rem;color:var(--g700);font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:5px">
      <i class="fa-solid fa-gauge"></i> Back to Dashboard
    </a>
  </div>

  @else
  {{-- ── REQUEST FORM (no active session) ── --}}

  {{-- Pending requests notice --}}
  @php
    $pendingResearch = \App\Models\ServiceRequest::where('user_id',$user->id)
                       ->where('service_type','research')
                       ->whereIn('status',['pending','approved'])
                       ->first();
  @endphp

  @if($pendingResearch)
  <div class="abox info" style="margin-bottom:16px">
    <i class="fa-solid fa-hourglass-half"></i>
    <div>
      You have a <strong>{{ $pendingResearch->status }}</strong> research request
      ({{ $pendingResearch->request_number }}).
      @if($pendingResearch->status === 'approved')
        Please visit the IT Center — a PC will be assigned for you.
      @else
        Please wait for admin approval.
      @endif
    </div>
  </div>
  @endif

  <div style="background:var(--white);border-radius:16px;box-shadow:var(--shadow-sm);border:1.5px solid var(--gray200);overflow:hidden">

    <div style="background:linear-gradient(135deg,var(--g700),var(--g500));padding:18px 22px;display:flex;align-items:center;gap:12px">
      <div style="width:42px;height:42px;border-radius:10px;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.1rem;flex-shrink:0">
        <i class="fa-solid fa-desktop"></i>
      </div>
      <div>
        <div style="font-size:.95rem;font-weight:800;color:#fff">Research / Computer Lab Request</div>
        <div style="font-size:.72rem;color:rgba(255,255,255,.75)">Reserve a PC for academic research</div>
      </div>
    </div>

    <form action="{{ route('requests.research.store') }}" method="POST" style="padding:20px">
      @csrf

      <div class="fg">
        <div class="flabel"><i class="fa-solid fa-clock" style="color:var(--g600)"></i> Duration <span style="color:var(--red)">*</span></div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(100px,1fr));gap:10px">
          @forelse($durations as $d)
          <label style="cursor:pointer">
            <input type="radio" name="duration_minutes" value="{{ $d->value }}" style="display:none"
              required {{ old('duration_minutes')==$d->value?'checked':'' }}>
            <div class="dur-opt" style="border:1.5px solid var(--gray200);border-radius:12px;padding:14px 8px;text-align:center;background:var(--white);transition:all .2s">
              <div style="font-size:1.4rem;font-weight:800;color:var(--g700)">{{ $d->value }}</div>
              <div style="font-size:.68rem;color:var(--gray400);margin-top:2px">minutes</div>
            </div>
          </label>
          @empty
          <div class="abox warn" style="grid-column:1/-1">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <div>No time slots available. Please contact the IT Center.</div>
          </div>
          @endforelse
        </div>
      </div>

      <div id="duration-summary" style="display:none;background:var(--g100);border-radius:10px;padding:12px 14px;margin-bottom:14px">
        <div style="display:flex;align-items:center;gap:10px">
          <div style="width:36px;height:36px;border-radius:50%;background:var(--g500);display:flex;align-items:center;justify-content:center;color:#fff;flex-shrink:0;font-size:.85rem">
            <i class="fa-solid fa-clock"></i>
          </div>
          <div>
            <div style="font-size:.8rem;font-weight:700;color:var(--g700)">Selected: <span id="sel-duration">--</span></div>
            <div style="font-size:.72rem;color:var(--gray600)">Proceed to IT Center after your request is approved</div>
          </div>
        </div>
      </div>

      <div class="fg">
        <div class="flabel"><i class="fa-solid fa-pen-to-square" style="color:var(--g600)"></i> Purpose <span style="color:var(--red)">*</span></div>
        <textarea name="purpose" class="fc" rows="3"
          placeholder="Describe what you will use the computer for..."
          required style="resize:vertical">{{ old('purpose') }}</textarea>
      </div>

      <div class="abox info" style="margin-bottom:14px">
        <i class="fa-solid fa-circle-info"></i>
        <div>After approval, proceed to the IT Center within <strong>10 minutes</strong>. Bring your UCC ID.</div>
      </div>

      <div style="background:var(--gray100);border-radius:10px;padding:12px 14px;margin-bottom:14px;display:flex;align-items:center;gap:10px">
        <input type="checkbox" id="terms_check" name="terms" value="1"
          style="width:16px;height:16px;accent-color:var(--g600);cursor:pointer;flex-shrink:0"
          required {{ old('terms')?'checked':'' }}>
        <label for="terms_check" style="font-size:.76rem;color:var(--gray600);cursor:pointer;line-height:1.4">
          I have read and agree to the
          <a href="#" onclick="openModal('resTerms');return false;" style="color:var(--g700);font-weight:700">Computer Lab Terms & Conditions</a>
        </label>
      </div>

      <button type="submit" class="btn">
        <i class="fa-solid fa-paper-plane"></i> Submit Research Request
      </button>
    </form>
  </div>
  @endif
</div>

@push('styles')
<style>
input[type=radio]:checked + .dur-opt {
  border-color: var(--g500) !important;
  background: var(--g100) !important;
  transform: scale(1.03);
}
.dur-opt:hover { border-color: var(--g400) !important; }
input[type=radio]:checked + .dur-radio-opt {
  border-color: var(--g500) !important;
  background: var(--g100) !important;
}
</style>
@endpush

@push('scripts')
<script>
function openModal(id){ document.getElementById(id).classList.add('open') }
function closeModal(id){ document.getElementById(id).classList.remove('open') }
function acceptTerms(m,c){ document.getElementById(c).checked = true; closeModal(m) }
document.querySelectorAll('.modal-bg').forEach(m=>m.addEventListener('click',e=>{if(e.target===m)m.classList.remove('open')}));

document.querySelectorAll('input[name=duration_minutes]').forEach(r => {
  r.addEventListener('change', () => {
    const s = document.getElementById('duration-summary');
    if (s) {
      s.style.display = 'block';
      document.getElementById('sel-duration').textContent = r.value + ' minutes';
    }
  });
});

@if($activeSession)
const TOTAL_SEC = {{ $activeSession->total_minutes * 60 }};
let remaining   = {{ $activeSession->remaining_seconds }};
let warned      = false;
let alarmed     = false;

function fmt(s){ const m=Math.floor(s/60),sec=s%60; return String(m).padStart(2,'0')+':'+String(sec).padStart(2,'0'); }

function playBeep(freq=880, dur=0.3, vol=0.4) {
  try {
    const ctx = new (window.AudioContext||window.webkitAudioContext)();
    const osc = ctx.createOscillator();
    const gain= ctx.createGain();
    osc.connect(gain); gain.connect(ctx.destination);
    osc.type = 'sine'; osc.frequency.value = freq;
    gain.gain.setValueAtTime(vol, ctx.currentTime);
    gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime+dur);
    osc.start(ctx.currentTime); osc.stop(ctx.currentTime+dur);
  } catch(e){}
}

function speakAlert(text) {
  if ('speechSynthesis' in window) {
    window.speechSynthesis.cancel();
    const u = new SpeechSynthesisUtterance(text);
    u.lang = 'en-US'; u.rate = 0.9; u.pitch = 1; u.volume = 1;
    window.speechSynthesis.speak(u);
  }
}

function tick() {
  if (remaining < 0) remaining = 0;

  const el  = document.getElementById('countdown');
  const bar = document.getElementById('progress-bar');

  if (el) {
    el.textContent = fmt(remaining);
    if (remaining <= 60)       { el.style.color = 'var(--red)'; }
    else if (remaining <= 300) { el.style.color = 'var(--orange)'; }
    else                       { el.style.color = 'var(--g700)'; }
  }

  if (bar) {
    const pct = TOTAL_SEC > 0 ? (remaining / TOTAL_SEC * 100) : 0;
    bar.style.width = pct + '%';
    if (remaining <= 60)       bar.style.background = 'linear-gradient(90deg,var(--red),#ff6b6b)';
    else if (remaining <= 300) bar.style.background = 'linear-gradient(90deg,var(--orange),#ffb74d)';
    else                       bar.style.background = 'linear-gradient(90deg,var(--g500),var(--g300))';
  }

  // 5-minute warning
  if (remaining <= 300 && !warned) {
    warned = true;
    playBeep(660, 0.25); setTimeout(() => playBeep(660, 0.25), 300);
    speakAlert('Warning. 5 minutes remaining on {{ $activeSession->computer->name }}.');
  }

  // Time's up
  if (remaining <= 0 && !alarmed) {
    alarmed = true;
    playBeep(880, 0.3); setTimeout(()=>playBeep(660,0.3),350); setTimeout(()=>playBeep(440,0.6),700);
    speakAlert('Time is up for {{ $user->first_name }} {{ $user->last_name }} on {{ $activeSession->computer->name }}. Please log off and return to the IT Center.');
    const notice = document.getElementById('expired-notice');
    if (notice) notice.style.display = 'block';
  }

  if (remaining > 0) { remaining--; setTimeout(tick, 1000); }
}

tick();

// Sync every 30s
setInterval(() => {
  fetch('{{ route('admin.service-requests.session-status', $activeSession->serviceRequest) }}')
    .then(r=>r.json())
    .then(d=>{
      remaining = d.remaining_seconds;
      const ea = document.getElementById('ends-at-display');
      if (ea) ea.textContent = d.ends_at;
    }).catch(()=>{});
}, 30000);
@endif
</script>
@endpush
@endsection