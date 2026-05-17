@php $unread = \App\Models\AdminNotification::where('is_read',false)->count(); @endphp

<div class="topbar">
  <div>
    <h1>{{ $title }}</h1>
    <p>{{ $sub ?? '' }}</p>
  </div>
  <div class="topbar-right">
    <div class="clock">
      <i class="fa-solid fa-clock" style="color:var(--g600)"></i>
      <span id="clock">--:-- --</span>
    </div>
    <a href="{{ route('admin.notifications') }}" class="notif-wrap"
       style="color:var(--gray600);position:relative;text-decoration:none;display:flex;align-items:center">
      <i class="fa-solid fa-bell" style="font-size:1.1rem"></i>
      <span id="notif-badge"
            class="notif-badge"
            style="{{ $unread ? '' : 'display:none' }}">{{ $unread }}</span>
    </a>
  </div>
</div>

{{-- TOAST CONTAINER --}}
<div id="toast-container"
     style="position:fixed;top:20px;right:20px;z-index:9999;
            display:flex;flex-direction:column;gap:8px;max-width:340px;pointer-events:none">
</div>

@push('scripts')
<script>
// ── CLOCK ──
if (!window._clockRunning) {
  window._clockRunning = true;
  (function tick(){
    const n=new Date(), h=n.getHours(), m=n.getMinutes(), s=n.getSeconds();
    const ap=h>=12?'PM':'AM', h12=h%12||12;
    const el=document.getElementById('clock');
    if(el) el.textContent=String(h12).padStart(2,'0')+':'+String(m).padStart(2,'0')+':'+String(s).padStart(2,'0')+' '+ap;
    setTimeout(tick,1000);
  })();
}

// ── NOTIFICATION SOUND ──
function playNotifSound(){
  try{
    const c=new(window.AudioContext||window.webkitAudioContext)();
    [880,1100,880].forEach((f,i)=>{
      const o=c.createOscillator(), g=c.createGain();
      o.connect(g); g.connect(c.destination);
      o.type='sine'; o.frequency.value=f;
      g.gain.setValueAtTime(0.3,c.currentTime+i*0.15);
      g.gain.exponentialRampToValueAtTime(0.001,c.currentTime+i*0.15+0.12);
      o.start(c.currentTime+i*0.15);
      o.stop(c.currentTime+i*0.15+0.15);
    });
  }catch(e){}
}

// ── TOAST ──
function showAdminToast(title, message, icon='fa-bell', type='info'){
  const c = document.getElementById('toast-container');
  const t = document.createElement('div');
  const colors={info:'var(--g500)',warn:'var(--orange)',err:'var(--red)',ok:'var(--g500)'};
  const border = colors[type] || colors.info;
  t.style.cssText=`pointer-events:auto;background:#fff;border-radius:12px;
    box-shadow:0 8px 28px rgba(0,0,0,.18);padding:14px 16px;
    border-left:4px solid ${border};
    animation:fadeUp .35s cubic-bezier(.16,1,.3,1);
    display:flex;gap:10px;align-items:flex-start;
    transition:opacity .3s`;
  t.innerHTML=`
    <div style="width:34px;height:34px;border-radius:9px;flex-shrink:0;
      background:${border}22;display:flex;align-items:center;justify-content:center;color:${border};font-size:.9rem">
      <i class="fa-solid ${icon}"></i>
    </div>
    <div style="flex:1;min-width:0">
      <div style="font-size:.8rem;font-weight:800;color:var(--gray800);margin-bottom:2px">${title}</div>
      <div style="font-size:.73rem;color:var(--gray600);line-height:1.4">${message}</div>
    </div>
    <button onclick="this.closest('div[style]').remove()"
      style="background:none;border:none;color:var(--gray400);cursor:pointer;font-size:.9rem;flex-shrink:0;padding:2px">
      <i class="fa-solid fa-xmark"></i>
    </button>`;
  c.appendChild(t);
  setTimeout(()=>{ if(t.parentNode){ t.style.opacity='0'; setTimeout(()=>t.remove(),300); } }, 6000);
}

// ── REAL-TIME NOTIFICATION POLLING ──
let lastNotifId = {{ \App\Models\AdminNotification::max('id') ?? 0 }};
let lastCount   = {{ $unread }};

function pollNotifications(){
  fetch('{{ route("admin.notifications.poll") }}?last_id='+lastNotifId)
    .then(r=>r.json())
    .then(data=>{
      // Update badge
      const badge = document.getElementById('notif-badge');
      if (data.unread_count > 0) {
        badge.textContent = data.unread_count;
        badge.style.display = 'block';
      } else {
        badge.style.display = 'none';
      }

      // Show toasts for new notifications
      if (data.notifications && data.notifications.length > 0) {
        data.notifications.forEach(n => {
          playNotifSound();
          showAdminToast(n.title, n.message, n.icon || 'fa-bell', 'info');
        });
        lastNotifId = Math.max(lastNotifId, ...data.notifications.map(n=>n.id));
      }

      if (data.last_id > lastNotifId) lastNotifId = data.last_id;
    })
    .catch(()=>{});
}

// Poll every 8 seconds for real-time feel
setInterval(pollNotifications, 8000);
</script>
@endpush