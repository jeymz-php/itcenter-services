@extends('layouts.app')
@section('title','Guest Request Details | Admin')
@section('body-class','dash-page')
@section('content')

{{-- REJECT MODAL --}}
<div class="modal-bg" id="rejectModal">
  <div class="modal-box">
    <div class="modal-hd">
      <h3><i class="fa-solid fa-xmark" style="color:var(--red);margin-right:6px"></i>Reject Guest Request</h3>
      <button class="modal-close" onclick="closeModal('rejectModal')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form action="{{ route('admin.guest-requests.reject', $gr) }}" method="POST">
      @csrf
      <div class="modal-body">
        <div class="fg">
          <div class="flabel">Reason for Rejection</div>
          <textarea name="admin_note" class="fc" rows="3" required style="resize:vertical"
            placeholder="State the reason..."></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="modal-btn secondary" onclick="closeModal('rejectModal')">Cancel</button>
        <button type="submit" class="modal-btn danger"><i class="fa-solid fa-xmark"></i> Reject</button>
      </div>
    </form>
  </div>
</div>

{{-- ASSIGN PC MODAL --}}
@if($gr->service_type === 'research' && $gr->status === 'approved')
<div class="modal-bg" id="assignPcModal">
  <div class="modal-box">
    <div class="modal-hd">
      <h3><i class="fa-solid fa-desktop" style="color:var(--g600);margin-right:6px"></i>Assign PC to Guest</h3>
      <button class="modal-close" onclick="closeModal('assignPcModal')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form action="{{ route('admin.guest-requests.assign-pc', $gr) }}" method="POST">
      @csrf
      <div class="modal-body">
        <div class="abox info" style="margin-bottom:14px">
          <i class="fa-solid fa-circle-info"></i>
          <div>Assigning a PC will <strong>immediately start</strong> the {{ $gr->duration_minutes }}-minute session timer for <strong>{{ $gr->full_name }}</strong>.</div>
        </div>
        <div class="fg">
          <div class="flabel">Select Available PC</div>
          @if($computers->count())
          <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px">
            @foreach($computers as $pc)
            <label style="cursor:pointer">
              <input type="radio" name="computer_id" value="{{ $pc->id }}" style="display:none" required>
              <div class="pc-opt" style="border:1.5px solid var(--gray200);border-radius:10px;padding:12px 8px;text-align:center;background:var(--white);transition:all .2s">
                <i class="fa-solid fa-computer" style="font-size:1.2rem;color:var(--g600);margin-bottom:5px"></i>
                <div style="font-size:.78rem;font-weight:800">{{ $pc->name }}</div>
                <div style="font-size:.65rem;color:var(--gray400);margin-top:2px">{{ Str::limit($pc->specs??'',25) }}</div>
                <span class="tag tag-active" style="margin-top:5px;font-size:.6rem">AVAILABLE</span>
              </div>
            </label>
            @endforeach
          </div>
          @else
          <div class="abox warn">
            <i class="fa-solid fa-triangle-exclamation"></i>
            No PCs available right now.
          </div>
          @endif
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="modal-btn secondary" onclick="closeModal('assignPcModal')">Cancel</button>
        @if($computers->count())
        <button type="submit" class="modal-btn primary">
          <i class="fa-solid fa-play"></i> Assign & Start Session
        </button>
        @endif
      </div>
    </form>
  </div>
</div>
@endif

<div class="dash-wrap">
  @include('admin.partials.sidebar')
  <main class="main">
    @include('admin.partials.topbar', [
      'title' => 'Guest Request Details',
      'sub'   => $gr->request_number,
    ])
    <div class="content">

      @if(session('success'))
        <div class="abox ok" style="margin-bottom:14px">
          <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
      @endif
      @if($errors->any())
        <div class="abox err" style="margin-bottom:14px">
          <i class="fa-solid fa-triangle-exclamation"></i> {{ $errors->first() }}
        </div>
      @endif

      <div style="display:grid;grid-template-columns:320px 1fr;gap:16px;align-items:start">

        {{-- LEFT: Request Info --}}
        <div>
          <div class="profile-card">
            <div class="profile-card-hd"
              style="background:{{ $gr->service_type==='printing'?'var(--blue)':($gr->service_type==='photocopy'?'var(--orange)':'var(--g600)') }};
                     color:#fff;border-radius:12px 12px 0 0;margin:-1px -1px 0">
              <i class="fa-solid {{ $gr->service_icon }}" style="color:#fff"></i>
              {{ ucfirst($gr->service_type) }} — {{ $gr->request_number }}
            </div>
            <div class="profile-card-body" style="padding:0">
              @php
                $rows = [
                  ['Status',    strtoupper($gr->status),                   'fa-circle-dot'],
                  ['Role',      ucfirst(str_replace('_',' ',$gr->role)),   'fa-user-tag'],
                  ['Name',      $gr->full_name,                            'fa-user'],
                  ['Email',     $gr->email,                                'fa-envelope'],
                  ['Campus',    config('campuses.'.$gr->campus,'—'),       'fa-building-columns'],
                  ['Submitted', $gr->created_at->format('M d, Y g:i A'),  'fa-calendar'],
                ];
                if ($gr->id_number)        $rows[] = ['ID Number',   $gr->id_number,                                'fa-id-card'];
                if ($gr->paper_size)       $rows[] = ['Paper Size',  strtoupper($gr->paper_size),                  'fa-expand'];
                if ($gr->copies)           $rows[] = ['Copies',      $gr->copies,                                  'fa-hashtag'];
                if ($gr->print_type)       $rows[] = ['Print Type',  ucfirst(str_replace('_',' ',$gr->print_type)),'fa-palette'];
                if ($gr->duration_minutes) $rows[] = ['Duration',    $gr->duration_minutes.' minutes',             'fa-clock'];
                if ($gr->computer)         $rows[] = ['PC Assigned', $gr->computer->name,                          'fa-computer'];
                $rows[] = ['Purpose', $gr->purpose, 'fa-pen'];
                if ($gr->admin_note) $rows[] = ['Admin Note', $gr->admin_note, 'fa-note-sticky'];
              @endphp

              @foreach($rows as [$lbl,$val,$ico])
              <div style="padding:10px 18px;border-bottom:1px solid var(--gray100);display:flex;gap:10px;align-items:flex-start">
                <i class="fa-solid {{ $ico }}" style="color:var(--g600);width:14px;font-size:.78rem;margin-top:2px;flex-shrink:0"></i>
                <div>
                  <div style="font-size:.64rem;color:var(--gray400);font-weight:600;text-transform:uppercase">{{ $lbl }}</div>
                  <div style="font-size:.78rem;font-weight:600;color:var(--gray800);margin-top:1px">{{ $val }}</div>
                </div>
              </div>
              @endforeach

              @if($gr->file_path)
              <div style="padding:12px 18px">
                <a href="{{ Storage::url($gr->file_path) }}" target="_blank"
                   class="btn" style="padding:9px;font-size:.78rem">
                  <i class="fa-solid fa-download"></i> Download File
                </a>
              </div>
              @endif
            </div>
          </div>
        </div>

        {{-- RIGHT: Actions + Timer --}}
        <div>

          {{-- ACTION BUTTONS --}}
          <div class="profile-card" style="margin-bottom:14px">
            <div class="profile-card-hd"><i class="fa-solid fa-bolt"></i> Actions</div>
            <div class="profile-card-body" style="display:flex;flex-wrap:wrap;gap:8px">

              @if($gr->status === 'pending')
                <form action="{{ route('admin.guest-requests.approve', $gr) }}" method="POST">
                  @csrf
                  <button class="btn" style="padding:9px 18px;font-size:.8rem;width:auto">
                    <i class="fa-solid fa-check"></i> Approve
                  </button>
                </form>
                <button class="btn"
                  style="background:linear-gradient(135deg,var(--red),#c62828);padding:9px 18px;font-size:.8rem;width:auto"
                  onclick="openModal('rejectModal')">
                  <i class="fa-solid fa-xmark"></i> Reject
                </button>
              @endif

              @if($gr->status === 'approved' && $gr->service_type === 'research')
                <button class="btn" style="padding:9px 18px;font-size:.8rem;width:auto"
                  onclick="openModal('assignPcModal')">
                  <i class="fa-solid fa-desktop"></i> Assign PC & Start Session
                </button>
              @endif

              @if($gr->status === 'approved' && $gr->service_type !== 'research')
                <form action="{{ route('admin.guest-requests.processing', $gr) }}" method="POST">
                  @csrf
                  <button class="btn"
                    style="background:linear-gradient(135deg,var(--blue),#1976d2);padding:9px 18px;font-size:.8rem;width:auto">
                    <i class="fa-solid fa-gear"></i> Mark Processing
                  </button>
                </form>
              @endif

              @if($gr->status === 'processing' && $gr->service_type !== 'research')
                <form action="{{ route('admin.guest-requests.complete', $gr) }}" method="POST">
                  @csrf
                  <button class="btn" style="padding:9px 18px;font-size:.8rem;width:auto">
                    <i class="fa-solid fa-check-double"></i> Mark Completed
                  </button>
                </form>
              @endif

              @if($session && in_array($session->status,['active','extended']))
                <form action="{{ route('admin.guest-requests.end-session', $gr) }}" method="POST"
                  onsubmit="return confirm('End session and mark as completed?')">
                  @csrf
                  <button class="btn"
                    style="background:linear-gradient(135deg,var(--red),#c62828);padding:9px 18px;font-size:.8rem;width:auto">
                    <i class="fa-solid fa-stop"></i> End Session Now
                  </button>
                </form>
              @endif

            </div>
          </div>

          {{-- SESSION TIMER --}}
          @if($gr->service_type === 'research' && $session && in_array($session->status,['active','extended']))
          <div class="profile-card" style="margin-bottom:14px">
            <div class="profile-card-hd">
              <i class="fa-solid fa-stopwatch" style="color:var(--g600)"></i> Live Session Timer
              <span class="tag tag-active" style="margin-left:auto">{{ strtoupper($session->status) }}</span>
            </div>
            <div class="profile-card-body" style="text-align:center">
              <div style="font-size:.78rem;color:var(--gray600);margin-bottom:6px">
                <i class="fa-solid fa-computer"></i> {{ $session->computer->name }}
                &nbsp;|&nbsp;
                <i class="fa-solid fa-clock"></i> Ends at
                <strong id="ends-at">{{ $session->ends_at?->format('g:i A') }}</strong>
              </div>
              <div id="countdown-display"
                style="font-size:3.5rem;font-weight:800;color:var(--g700);
                       font-variant-numeric:tabular-nums;letter-spacing:-2px;
                       margin:12px 0;line-height:1">
                --:--
              </div>
              <div style="font-size:.75rem;color:var(--gray400)">Time Remaining</div>
              <div style="background:var(--gray200);border-radius:10px;height:8px;margin:14px 0;overflow:hidden">
                <div id="progress-bar"
                     style="height:100%;border-radius:10px;
                            background:linear-gradient(90deg,var(--g500),var(--g300));
                            transition:width 1s linear;width:100%">
                </div>
              </div>
              <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;font-size:.72rem">
                <div style="background:var(--g100);border-radius:8px;padding:8px">
                  <div style="font-weight:700;color:var(--g700)">{{ $session->duration_minutes }}m</div>
                  <div style="color:var(--gray400)">Base</div>
                </div>
                <div style="background:{{ $session->extended_minutes?'var(--orange-bg)':'var(--gray100)' }};border-radius:8px;padding:8px">
                  <div style="font-weight:700;color:{{ $session->extended_minutes?'var(--orange)':'var(--gray400)' }}">{{ $session->extended_minutes }}m</div>
                  <div style="color:var(--gray400)">Extended</div>
                </div>
                <div style="background:var(--blue-bg);border-radius:8px;padding:8px">
                  <div style="font-weight:700;color:var(--blue)">{{ $session->total_minutes }}m</div>
                  <div style="color:var(--gray400)">Total</div>
                </div>
              </div>
            </div>
          </div>
          @endif

          {{-- TIMELINE --}}
          <div class="profile-card">
            <div class="profile-card-hd"><i class="fa-solid fa-timeline"></i> Request Timeline</div>
            <div class="profile-card-body" style="padding:14px 18px">
              @php
                $steps   = [
                  ['pending',    'Submitted',  $gr->created_at->format('M d, Y g:i A')],
                  ['approved',   'Approved',   $gr->reviewed_at?->format('M d, Y g:i A') ?? '—'],
                  ['processing', 'Processing', $session?->started_at?->format('M d, Y g:i A') ?? '—'],
                  ['completed',  'Completed',  $gr->updated_at->format('M d, Y g:i A')],
                ];
                $order   = ['pending'=>0,'approved'=>1,'processing'=>2,'completed'=>3,'rejected'=>1];
                $current = $order[$gr->status] ?? 0;
              @endphp
              @foreach($steps as $i => [$key,$label,$time])
              <div style="display:flex;gap:12px;align-items:flex-start;{{ !$loop->last?'padding-bottom:14px':'' }}">
                <div style="display:flex;flex-direction:column;align-items:center;flex-shrink:0">
                  <div style="width:28px;height:28px;border-radius:50%;
                    background:{{ $i<=$current?'var(--g500)':'var(--gray200)' }};
                    display:flex;align-items:center;justify-content:center;
                    color:{{ $i<=$current?'#fff':'var(--gray400)' }};font-size:.72rem">
                    <i class="fa-solid {{ $i<$current?'fa-check':($i===$current?'fa-circle-dot':'fa-circle') }}"></i>
                  </div>
                  @if(!$loop->last)
                  <div style="width:2px;flex:1;min-height:14px;
                    background:{{ $i<$current?'var(--g300)':'var(--gray200)' }};margin:3px 0">
                  </div>
                  @endif
                </div>
                <div style="padding-top:4px">
                  <div style="font-size:.78rem;font-weight:{{ $i===$current?'800':'600' }};
                    color:{{ $i<=$current?'var(--gray800)':'var(--gray400)' }}">{{ $label }}</div>
                  <div style="font-size:.68rem;color:var(--gray400);margin-top:1px">{{ $time }}</div>
                </div>
              </div>
              @endforeach
            </div>
          </div>

        </div>
      </div>
    </div>
  </main>
</div>

<div id="toast-container"
     style="position:fixed;top:20px;right:20px;z-index:9999;
            display:flex;flex-direction:column;gap:8px;max-width:340px">
</div>

@push('styles')
<style>
input[type=radio]:checked + .pc-opt {
  border-color: var(--g500) !important;
  background: var(--g100) !important;
}
.pc-opt:hover { border-color: var(--g400); background: var(--g50); }
</style>
@endpush

@push('scripts')
<script>
function openModal(id)  { document.getElementById(id).classList.add('open') }
function closeModal(id) { document.getElementById(id).classList.remove('open') }
document.querySelectorAll('.modal-bg').forEach(m =>
  m.addEventListener('click', e => { if(e.target===m) m.classList.remove('open') })
);

@if($session && in_array($session->status ?? '', ['active','extended']))
const TOTAL_SEC  = {{ $session->total_minutes * 60 }};
const PC_NAME    = '{{ $session->computer->name ?? "the PC" }}';
const GUEST_NAME = '{{ $gr->full_name }}';
let remaining    = {{ $session->remaining_seconds }};
let warned = false, alarmed = false;

function playBeep(f=880,d=0.4,v=0.5){
  try{const c=new(window.AudioContext||window.webkitAudioContext)();const o=c.createOscillator();const g=c.createGain();o.connect(g);g.connect(c.destination);o.type='sine';o.frequency.value=f;g.gain.setValueAtTime(v,c.currentTime);g.gain.exponentialRampToValueAtTime(0.001,c.currentTime+d);o.start(c.currentTime);o.stop(c.currentTime+d);}catch(e){}
}

function speak(text){
  if(!('speechSynthesis' in window)) return;
  window.speechSynthesis.cancel();
  const u = new SpeechSynthesisUtterance(text);
  u.lang='en-US'; u.rate=0.88; u.pitch=1; u.volume=1;
  const voices = window.speechSynthesis.getVoices();
  const v = voices.find(v=>v.lang.startsWith('en'));
  if(v) u.voice = v;
  window.speechSynthesis.speak(u);
}

function showToast(msg, type='ok'){
  const c = document.getElementById('toast-container');
  const t = document.createElement('div');
  const colors={ok:'var(--g500)',warn:'var(--orange)',err:'var(--red)',info:'var(--blue)'};
  t.style.cssText=`background:#fff;border-radius:12px;box-shadow:0 8px 24px rgba(0,0,0,.15);padding:14px 16px;border-left:4px solid ${colors[type]||colors.info};animation:fadeUp .3s ease;display:flex;gap:10px;align-items:flex-start`;
  t.innerHTML=`<i class="fa-solid fa-bell" style="color:${colors[type]};margin-top:2px;flex-shrink:0"></i><div style="flex:1;font-size:.8rem;font-weight:700;color:var(--gray800)">${msg}</div><button onclick="this.closest('div').remove()" style="background:none;border:none;color:var(--gray400);cursor:pointer">✕</button>`;
  c.appendChild(t);
  setTimeout(()=>{ if(t.parentNode){t.style.opacity='0';setTimeout(()=>t.remove(),300);} },6000);
}

function fmt(s){ const m=Math.floor(s/60),sec=s%60; return String(m).padStart(2,'0')+':'+String(sec).padStart(2,'0'); }

function tick(){
  if(remaining<0) remaining=0;
  const el  = document.getElementById('countdown-display');
  const bar = document.getElementById('progress-bar');
  if(el){
    el.textContent = fmt(remaining);
    el.style.color = remaining<=60?'var(--red)':remaining<=300?'var(--orange)':'var(--g700)';
  }
  if(bar){
    const pct = TOTAL_SEC>0?(remaining/TOTAL_SEC*100):0;
    bar.style.width = pct+'%';
    bar.style.background = remaining<=60
      ?'linear-gradient(90deg,var(--red),#ff6b6b)'
      :remaining<=300
        ?'linear-gradient(90deg,var(--orange),#ffb74d)'
        :'linear-gradient(90deg,var(--g500),var(--g300))';
  }
  if(remaining<=300 && !warned){
    warned=true;
    playBeep(660,0.25); setTimeout(()=>playBeep(660,0.25),300);
    speak(`Attention. ${GUEST_NAME} has 5 minutes remaining on ${PC_NAME}.`);
    showToast(`⚠️ ${GUEST_NAME} — 5 minutes remaining on ${PC_NAME}!`,'warn');
  }
  if(remaining<=0 && !alarmed){
    alarmed=true;
    playBeep(880,0.3); setTimeout(()=>playBeep(660,0.3),350); setTimeout(()=>playBeep(440,0.6),700);
    speak(`Time is up. ${GUEST_NAME} on ${PC_NAME} has exceeded their session time. Please assist the user.`);
    showToast(`🔔 Time's up! ${GUEST_NAME} on ${PC_NAME}`,'err');
  }
  if(remaining>0){ remaining--; setTimeout(tick,1000); }
}

window.speechSynthesis.onvoiceschanged = () => window.speechSynthesis.getVoices();
window.speechSynthesis.getVoices();
tick();
setInterval(()=>{
  fetch('{{ route("admin.guest-requests.session-status", $gr) }}')
    .then(r=>r.json()).then(d=>{
      remaining=d.remaining_seconds;
      const ea=document.getElementById('ends-at');
      if(ea) ea.textContent=d.ends_at;
    }).catch(()=>{});
},30000);
@endif
</script>
@endpush
@endsection