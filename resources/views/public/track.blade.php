<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Track Request | UCC IT Center</title>
<link rel="icon" type="image/x-icon" href="{{ asset('images/UCC_Logo.ico') }}">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{box-sizing:border-box;margin:0;padding:0}
:root{
  --g900:#0a3323;--g800:#124530;--g700:#18633f;--g600:#1e7d4f;
  --g500:#249660;--g400:#2db877;--g300:#5fce9b;--g200:#a8e8cc;
  --g100:#e4f7ef;--g50:#f2fbf7;
  --white:#fff;--offwhite:#f5f7f6;
  --gray100:#f0f4f2;--gray200:#dde6e2;--gray400:#8aa89f;
  --gray600:#4d6b61;--gray700:#3d5550;--gray800:#1e3530;
  --blue:#1565c0;--blue-bg:#e3f2fd;
  --orange:#e67e00;--orange-bg:#fff3e0;
  --red:#e53e3e;--red-bg:#fff0f0;
  --shadow-md:0 4px 18px rgba(10,51,35,.13);
  --shadow-lg:0 16px 48px rgba(10,51,35,.22);
  --rs:8px;
}
html,body{font-family:'Plus Jakarta Sans',sans-serif;min-height:100vh}
body{background:linear-gradient(135deg,var(--g800) 0%,var(--g600) 100%);
     display:flex;flex-direction:column;align-items:center;
     justify-content:flex-start;padding:32px 20px 48px}

.card{background:var(--white);border-radius:20px;box-shadow:var(--shadow-lg);
      padding:28px 28px;max-width:560px;width:100%;animation:fadeUp .4s ease}
@keyframes fadeUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}

.brand{display:flex;align-items:center;gap:10px;margin-bottom:22px}
.brand img{width:38px;height:38px;border-radius:9px;background:var(--g100);padding:3px;object-fit:contain}
.brand-text h1{font-size:1.1rem;font-weight:800;color:var(--gray800)}
.brand-text p{font-size:.74rem;color:var(--gray600);margin-top:1px}

.fg{margin-bottom:14px}
.flabel{font-size:.74rem;font-weight:600;color:var(--gray600);
        margin-bottom:5px;display:flex;align-items:center;gap:6px}
.fc{width:100%;padding:11px 14px;border:1.5px solid var(--gray200);
    border-radius:var(--rs);font-family:inherit;font-size:.88rem;
    color:var(--gray800);background:var(--gray100);outline:none;transition:all .2s}
.fc:focus{border-color:var(--g500);background:var(--white);
          box-shadow:0 0 0 3px rgba(36,150,96,.12)}
.btn{width:100%;padding:12px;
     background:linear-gradient(135deg,var(--g700),var(--g500));
     color:#fff;border:none;border-radius:var(--rs);
     font-family:inherit;font-size:.88rem;font-weight:700;cursor:pointer;
     display:flex;align-items:center;justify-content:center;gap:7px;
     transition:all .2s;box-shadow:0 4px 12px rgba(24,99,63,.3)}
.btn:hover{opacity:.92;transform:translateY(-1px)}

.tag{display:inline-flex;align-items:center;gap:4px;padding:4px 10px;
     border-radius:8px;font-size:.72rem;font-weight:700}
.tag-pend{background:var(--orange-bg);color:var(--orange)}
.tag-appr{background:var(--blue-bg);color:var(--blue)}
.tag-res{background:var(--g100);color:var(--g700)}
.tag-done{background:var(--g100);color:var(--g500)}
.tag-rej{background:var(--red-bg);color:var(--red)}

.detail-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px 16px;margin-top:14px}
.detail-item .lbl{font-size:.62rem;color:var(--gray400);font-weight:700;
                   text-transform:uppercase;margin-bottom:2px;letter-spacing:.04em}
.detail-item .val{font-size:.82rem;font-weight:700;color:var(--gray800)}

.abox{border-radius:var(--rs);padding:10px 14px;font-size:.78rem;
      display:flex;align-items:flex-start;gap:9px;margin-top:12px}
.abox.info{background:var(--g100);border-left:3px solid var(--g400);color:var(--g800)}
.abox.warn{background:var(--orange-bg);border-left:3px solid var(--orange);color:#7a5200}
.abox.err{background:var(--red-bg);border-left:3px solid var(--red);color:#7a1212}
.abox.ok{background:var(--g100);border-left:3px solid var(--g500);color:var(--g800)}

/* Session Timer Styles */
.session-card{
  background:linear-gradient(135deg,var(--g700),var(--g500));
  border-radius:14px;padding:20px;margin-top:16px;color:#fff;
}
.session-card h3{font-size:.9rem;font-weight:800;margin-bottom:4px;
                  display:flex;align-items:center;gap:8px}
.session-card .sub{font-size:.72rem;opacity:.8;margin-bottom:16px}
.countdown-big{
  font-size:3.2rem;font-weight:800;text-align:center;
  font-variant-numeric:tabular-nums;letter-spacing:-2px;
  line-height:1;margin:10px 0 4px;
}
.countdown-label{font-size:.7rem;text-align:center;opacity:.7;margin-bottom:14px}
.prog-wrap{background:rgba(255,255,255,.2);border-radius:6px;height:8px;
           overflow:hidden;margin-bottom:14px}
.prog-bar{height:100%;border-radius:6px;
          background:rgba(255,255,255,.85);
          transition:width 1s linear;width:100%}
.session-meta{display:grid;grid-template-columns:repeat(3,1fr);gap:8px}
.session-meta-item{background:rgba(255,255,255,.15);border-radius:9px;
                    padding:8px;text-align:center}
.session-meta-item .sm-val{font-size:.88rem;font-weight:800}
.session-meta-item .sm-lbl{font-size:.63rem;opacity:.7;margin-top:2px}

.expired-notice{
  background:var(--red-bg);border-radius:12px;padding:14px 16px;
  margin-top:12px;text-align:center;
}
.expired-notice i{font-size:1.4rem;color:var(--red);display:block;margin-bottom:8px}
.expired-notice p{font-size:.8rem;color:var(--red);font-weight:700}
.expired-notice small{font-size:.72rem;color:var(--gray600)}

.back-link{display:block;text-align:center;margin-top:16px;
           font-size:.76rem;color:rgba(255,255,255,.7);text-decoration:none}
.back-link:hover{color:#fff}

@media(max-width:480px){
  body{padding:20px 14px 40px}
  .card{padding:22px 18px}
  .countdown-big{font-size:2.4rem}
  .detail-grid{grid-template-columns:1fr}
}
</style>
</head>
<body>

<div class="card">
  <div class="brand">
    <img src="{{ asset('images/UCC_Logo.png') }}" alt="UCC">
    <div class="brand-text">
      <h1>Track Your Request</h1>
      <p>Enter your request number to check status</p>
    </div>
  </div>

  <form method="GET" action="{{ route('public.track') }}">
    <div class="fg">
      <div class="flabel">
        <i class="fa-solid fa-hashtag" style="color:var(--g600)"></i> Request Number
      </div>
      <input type="text" name="number" class="fc"
             placeholder="e.g. G-000001"
             value="{{ request('number') }}" required>
    </div>
    <button type="submit" class="btn">
      <i class="fa-solid fa-magnifying-glass"></i> Track Request
    </button>
  </form>

  {{-- NOT FOUND --}}
  @if(request('number') && !$gr)
  <div class="abox err" style="margin-top:14px">
    <i class="fa-solid fa-circle-xmark"></i>
    <div>No request found with number <strong>{{ request('number') }}</strong>.</div>
  </div>
  @endif

  {{-- FOUND --}}
  @if($gr)
  <div style="margin-top:20px;padding:16px;background:var(--g50);
              border-radius:12px;border:1.5px solid var(--gray200)">

    {{-- Header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;
                margin-bottom:10px;flex-wrap:wrap;gap:8px">
      <div>
        <div style="font-family:monospace;font-size:.95rem;font-weight:800;
                    color:var(--gray800)">{{ $gr->request_number }}</div>
        <div style="font-size:.7rem;color:var(--gray400);margin-top:2px">
          {{ $gr->created_at->format('M d, Y g:i A') }}
        </div>
      </div>
      @php
        $statusClass = [
          'pending'    =>'tag-pend','approved'=>'tag-appr',
          'processing' =>'tag-res', 'completed'=>'tag-done','rejected'=>'tag-rej'
        ];
      @endphp
      <span class="tag {{ $statusClass[$gr->status] ?? '' }}">
        {{ strtoupper($gr->status) }}
      </span>
    </div>

    {{-- Details --}}
    <div class="detail-grid">
      <div class="detail-item">
        <div class="lbl">Name</div>
        <div class="val">{{ $gr->full_name }}</div>
      </div>
      <div class="detail-item">
        <div class="lbl">Role</div>
        <div class="val">{{ ucfirst(str_replace('_',' ',$gr->role)) }}</div>
      </div>
      <div class="detail-item">
        <div class="lbl">Service</div>
        <div class="val">{{ ucfirst($gr->service_type) }}</div>
      </div>
      <div class="detail-item">
        <div class="lbl">Campus</div>
        <div class="val">{{ config('campuses.'.$gr->campus) }}</div>
      </div>
      @if($gr->copies)
      <div class="detail-item">
        <div class="lbl">Copies</div>
        <div class="val">{{ $gr->copies }}</div>
      </div>
      @endif
      @if($gr->paper_size)
      <div class="detail-item">
        <div class="lbl">Paper Size</div>
        <div class="val">{{ strtoupper($gr->paper_size) }}</div>
      </div>
      @endif
      @if($gr->duration_minutes)
      <div class="detail-item">
        <div class="lbl">Duration</div>
        <div class="val">{{ $gr->duration_minutes }} minutes</div>
      </div>
      @endif
      @if($gr->computer)
      <div class="detail-item">
        <div class="lbl">PC Assigned</div>
        <div class="val" style="color:var(--g700)">
          <i class="fa-solid fa-computer" style="font-size:.75rem"></i>
          {{ $gr->computer->name }}
        </div>
      </div>
      @endif
    </div>

    {{-- Admin note --}}
    @if($gr->admin_note)
    <div class="abox err">
      <i class="fa-solid fa-circle-xmark"></i>
      <div><strong>Admin Note:</strong> {{ $gr->admin_note }}</div>
    </div>
    @endif

    {{-- Status hints --}}
    @if($gr->status === 'approved' && $gr->service_type !== 'research')
    <div class="abox info">
      <i class="fa-solid fa-circle-check"></i>
      <div>Your request is approved! Please visit the IT Center to proceed.</div>
    </div>
    @elseif($gr->status === 'approved' && $gr->service_type === 'research')
    <div class="abox info">
      <i class="fa-solid fa-desktop"></i>
      <div>Your research request is approved. Please proceed to the IT Center — a PC will be assigned.</div>
    </div>
    @elseif($gr->status === 'completed')
    <div class="abox ok">
      <i class="fa-solid fa-check-double"></i>
      <div>Your request has been completed successfully!</div>
    </div>
    @endif

  </div>

  {{-- ── ACTIVE SESSION TIMER (Research only) ── --}}
  @if($gr->service_type === 'research' && $session && in_array($session->status, ['active','extended']))

  <div class="session-card" id="session-card">
    <h3>
      <i class="fa-solid fa-desktop"></i>
      Active PC Session
    </h3>
    <div class="sub">
      {{ $gr->computer->name ?? 'PC' }} ·
      Ends at <strong id="ends-at">{{ $session->ends_at?->format('g:i A') }}</strong>
      @if($session->extended_minutes)
        <span style="background:rgba(255,255,255,.2);border-radius:6px;padding:2px 7px;font-size:.65rem;margin-left:4px">
          +{{ $session->extended_minutes }}m extended
        </span>
      @endif
    </div>

    <div class="countdown-big" id="countdown">--:--</div>
    <div class="countdown-label">Time Remaining</div>

    <div class="prog-wrap">
      <div class="prog-bar" id="prog-bar"></div>
    </div>

    <div class="session-meta">
      <div class="session-meta-item">
        <div class="sm-val">{{ $session->duration_minutes }}m</div>
        <div class="sm-lbl">Base</div>
      </div>
      <div class="session-meta-item">
        <div class="sm-val" style="color:{{ $session->extended_minutes?'#ffcc80':'rgba(255,255,255,.5)' }}">
          {{ $session->extended_minutes }}m
        </div>
        <div class="sm-lbl">Extended</div>
      </div>
      <div class="session-meta-item">
        <div class="sm-val">{{ $session->total_minutes }}m</div>
        <div class="sm-lbl">Total</div>
      </div>
    </div>
  </div>

  {{-- Expired notice (shown when timer hits 0) --}}
  <div class="expired-notice" id="expired-notice" style="display:none">
    <i class="fa-solid fa-clock"></i>
    <p>Session Time is Up!</p>
    <small>Please log off the PC and return to the IT Center staff.</small>
  </div>

  @elseif($gr->service_type === 'research' && $session && $session->status === 'completed')
  <div class="abox ok" style="margin-top:12px">
    <i class="fa-solid fa-check-double"></i>
    <div>
      PC session completed.
      {{ $session->started_at?->format('g:i A') }} –
      {{ $session->ended_at?->format('g:i A') ?? $session->ends_at?->format('g:i A') }}
      ({{ $session->total_minutes }} minutes total)
    </div>
  </div>
  @endif

  @endif {{-- end $gr --}}
</div>

<a href="{{ route('public.request') }}" class="back-link">
  <i class="fa-solid fa-arrow-left"></i> Back to Request Form
</a>

@if($gr && $gr->service_type === 'research' && $session && in_array($session->status ?? '', ['active','extended']))
<script>
const TOTAL_SEC  = {{ $session->total_minutes * 60 }};
const PC_NAME    = '{{ $session->computer->name ?? "the PC" }}';
const GUEST_NAME = '{{ $gr->full_name }}';
const STATUS_URL = '{{ route("public.session-status", $gr) }}';
let remaining    = {{ $session->remaining_seconds }};
let warned = false, alarmed = false;

function fmt(s){
  const m = Math.floor(s/60), sec = s%60;
  return String(m).padStart(2,'0') + ':' + String(sec).padStart(2,'0');
}

function playBeep(f=880, d=0.3, v=0.4){
  try{
    const c = new (window.AudioContext||window.webkitAudioContext)();
    const o = c.createOscillator(), g = c.createGain();
    o.connect(g); g.connect(c.destination);
    o.type='sine'; o.frequency.value=f;
    g.gain.setValueAtTime(v, c.currentTime);
    g.gain.exponentialRampToValueAtTime(0.001, c.currentTime+d);
    o.start(c.currentTime); o.stop(c.currentTime+d);
  } catch(e){}
}

function speak(text){
  if(!('speechSynthesis' in window)) return;
  window.speechSynthesis.cancel();
  const u = new SpeechSynthesisUtterance(text);
  u.lang='en-US'; u.rate=0.88; u.pitch=1; u.volume=1;
  const voices = window.speechSynthesis.getVoices();
  const v = voices.find(v => v.lang.startsWith('en'));
  if(v) u.voice = v;
  window.speechSynthesis.speak(u);
}

function tick(){
  if(remaining < 0) remaining = 0;

  const cd  = document.getElementById('countdown');
  const bar = document.getElementById('prog-bar');

  if(cd){
    cd.textContent = fmt(remaining);
    if(remaining <= 60)       cd.style.color = '#ffcccc';
    else if(remaining <= 300) cd.style.color = '#ffe0a0';
    else                      cd.style.color = '#fff';
  }

  if(bar){
    const pct = TOTAL_SEC > 0 ? (remaining / TOTAL_SEC * 100) : 0;
    bar.style.width = pct + '%';
    if(remaining <= 60)
      bar.style.background = 'rgba(229,62,62,.9)';
    else if(remaining <= 300)
      bar.style.background = 'rgba(255,180,60,.9)';
    else
      bar.style.background = 'rgba(255,255,255,.85)';
  }

  // 5-minute warning
  if(remaining <= 300 && !warned){
    warned = true;
    playBeep(660, 0.25); setTimeout(()=>playBeep(660, 0.25), 300);
    speak(`Warning. 5 minutes remaining on ${PC_NAME}.`);
  }

  // Time's up
  if(remaining <= 0 && !alarmed){
    alarmed = true;
    playBeep(880,0.3); setTimeout(()=>playBeep(660,0.3),350); setTimeout(()=>playBeep(440,0.6),700);
    speak(`Time is up on ${PC_NAME}. Please log off and return to the IT Center.`);
    const sc = document.getElementById('session-card');
    const en = document.getElementById('expired-notice');
    if(sc) sc.style.display = 'none';
    if(en) en.style.display = 'block';
    return; // stop ticking
  }

  if(remaining > 0){ remaining--; setTimeout(tick, 1000); }
}

// Sync with server every 20s
function syncStatus(){
  fetch(STATUS_URL)
    .then(r => r.json())
    .then(d => {
      if(!d.active){
        // Session ended by admin
        const sc = document.getElementById('session-card');
        const en = document.getElementById('expired-notice');
        if(sc) sc.style.display = 'none';
        if(en){ en.style.display='block'; en.querySelector('p').textContent='Session Ended'; }
        return;
      }
      remaining = d.remaining_seconds;
      const ea = document.getElementById('ends-at');
      if(ea) ea.textContent = d.ends_at;
    })
    .catch(()=>{});
}

window.speechSynthesis.onvoiceschanged = () => window.speechSynthesis.getVoices();
window.speechSynthesis.getVoices();

tick();
setInterval(syncStatus, 20000);
</script>
@endif

</body>
</html>