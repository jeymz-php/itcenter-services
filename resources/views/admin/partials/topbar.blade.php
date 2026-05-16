@php $unread = \App\Models\AdminNotification::where('is_read',false)->count(); @endphp

<div class="topbar">
  <div>
    <h1>{{ $title }}</h1>
    <p>{{ $sub ?? '' }}</p>
  </div>
  <div class="topbar-right">
    <div class="clock"><i class="fa-solid fa-clock" style="color:var(--g600)"></i><span id="clock">--:-- --</span></div>
    <a href="{{ route('admin.notifications') }}" class="notif-wrap" style="color:var(--gray600);position:relative">
      <i class="fa-solid fa-bell" style="font-size:1.1rem"></i>
      <span id="notif-badge" class="notif-badge" style="{{ $unread?'':'display:none' }}">{{ $unread }}</span>
    </a>
  </div>
</div>

{{-- NOTIFICATION TOAST CONTAINER --}}
<div id="toast-container" style="position:fixed;top:20px;right:20px;z-index:9999;display:flex;flex-direction:column;gap:8px;max-width:340px"></div>

@push('scripts')
<script>
// Clock
(function tick(){
  const n=new Date(),h=n.getHours(),m=n.getMinutes(),s=n.getSeconds();
  const ap=h>=12?'PM':'AM',h12=h%12||12;
  const el=document.getElementById('clock');
  if(el)el.textContent=String(h12).padStart(2,'0')+':'+String(m).padStart(2,'0')+':'+String(s).padStart(2,'0')+' '+ap;
  setTimeout(tick,1000);
})();

// Notification sound
function playNotifSound() {
  try {
    const ctx = new (window.AudioContext || window.webkitAudioContext)();
    [880,1100,880].forEach((f,i)=>{
      const osc=ctx.createOscillator(), gain=ctx.createGain();
      osc.connect(gain); gain.connect(ctx.destination);
      osc.type='sine'; osc.frequency.value=f;
      gain.gain.setValueAtTime(0.3, ctx.currentTime+i*0.15);
      gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime+i*0.15+0.12);
      osc.start(ctx.currentTime+i*0.15);
      osc.stop(ctx.currentTime+i*0.15+0.15);
    });
  } catch(e){}
}

function showToast(title, message, type='info') {
  const c = document.getElementById('toast-container');
  const t = document.createElement('div');
  const colors = {info:'var(--g600)',warn:'var(--orange)',err:'var(--red)',ok:'var(--g500)'};
  t.style.cssText = `background:#fff;border-radius:12px;box-shadow:0 8px 24px rgba(0,0,0,.15);
    padding:14px 16px;border-left:4px solid ${colors[type]||colors.info};
    animation:fadeUp .3s ease;display:flex;gap:10px;align-items:flex-start;cursor:pointer`;
  t.innerHTML = `
    <i class="fa-solid fa-bell" style="color:${colors[type]||colors.info};margin-top:2px;flex-shrink:0"></i>
    <div style="flex:1;min-width:0">
      <div style="font-size:.8rem;font-weight:800;color:var(--gray800)">${title}</div>
      <div style="font-size:.73rem;color:var(--gray600);margin-top:2px;line-height:1.4">${message}</div>
    </div>
    <button onclick="this.closest('div[style]').remove()" style="background:none;border:none;color:var(--gray400);cursor:pointer;font-size:.85rem;flex-shrink:0">✕</button>`;
  t.addEventListener('click', ()=>t.remove());
  c.appendChild(t);
  setTimeout(()=>{ if(t.parentNode) t.style.opacity='0'; setTimeout(()=>t.remove(),300); }, 5000);
}

// Poll for new notifications every 15s
let lastNotifCount = {{ $unread }};
let lastNotifId = 0;

function pollNotifications() {
  fetch('{{ route("admin.notifications.count") }}')
    .then(r=>r.json())
    .then(d=>{
      const badge = document.getElementById('notif-badge');
      if (d.count > 0) {
        badge.textContent = d.count;
        badge.style.display = 'block';
      } else {
        badge.style.display = 'none';
      }
      if (d.count > lastNotifCount && lastNotifCount >= 0) {
        playNotifSound();
        if (d.latest) {
          showToast(d.latest.title, d.latest.message, 'info');
        }
      }
      lastNotifCount = d.count;
    })
    .catch(()=>{});
}

setInterval(pollNotifications, 15000);
</script>
@endpush