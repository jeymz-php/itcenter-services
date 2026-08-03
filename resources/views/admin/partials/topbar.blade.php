@php
  $topbarAdmin = session('admin');
  $adminNotificationScope = \App\Models\AdminNotification::query();
  if ($topbarAdmin && $topbarAdmin->role !== 'super_admin') {
      $adminNotificationScope->where(function ($query) use ($topbarAdmin) {
          $query->where('campus', $topbarAdmin->campus)->orWhereNull('campus');
      });
  }
  $unread = (clone $adminNotificationScope)->where('is_read', false)->count();
  $lastAdminNotificationId = (clone $adminNotificationScope)->max('id') ?? 0;
@endphp

<div class="topbar">
  <div>
    <h1>{{ $title }}</h1>
    <p>{{ $sub ?? '' }}</p>
  </div>
  <div class="topbar-right">
    <div class="clock">
      <i class="fa-solid fa-clock" style="color:var(--g600)"></i>
      <span id="clock" data-admin-live-clock title="Philippine Standard Time">--:--:-- --</span>
    </div>
    <a href="{{ route('admin.notifications') }}"
       id="admin-notification-toggle"
       class="notif-wrap floating-notification-toggle"
       aria-label="Open notifications"
       aria-haspopup="true"
       aria-expanded="false"
       title="Notifications">
      <i class="fa-solid fa-bell" style="font-size:1.1rem"></i>
      <span id="notif-badge" data-admin-notif-badge class="notif-badge" style="{{ $unread ? '' : 'display:none' }}">{{ $unread }}</span>
    </a>
  </div>
</div>

<div id="admin-notification-panel" class="floating-notification-panel" aria-hidden="true">
  <div class="floating-notification-header">
    <div>
      <strong>Notifications</strong>
      <small>Admin and service updates</small>
    </div>
    <button type="button" id="admin-mark-all-read" class="floating-notification-text-btn">Mark all read</button>
  </div>
  <div id="admin-notification-list" class="floating-notification-list" aria-live="polite">
    <div class="floating-notification-empty">
      <i class="fa-solid fa-spinner fa-spin"></i>
      <span>Loading notifications...</span>
    </div>
  </div>
  <a href="{{ route('admin.notifications') }}" class="floating-notification-footer">
    View all notifications <i class="fa-solid fa-arrow-right"></i>
  </a>
</div>

<div id="toast-container" class="admin-toast-container"></div>

@push('scripts')
<script>
(function () {
  if (window.__itcAdminClockReady) return;
  window.__itcAdminClockReady = true;

  const formatter = new Intl.DateTimeFormat('en-PH', {
    timeZone: 'Asia/Manila',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
    hour12: true
  });

  function updateAdminClock() {
    document.querySelectorAll('[data-admin-live-clock]').forEach(clock => {
      clock.textContent = formatter.format(new Date());
    });
  }

  updateAdminClock();
  window.__itcAdminClockInterval = window.setInterval(updateAdminClock, 1000);
  document.addEventListener('visibilitychange', () => {
    if (!document.hidden) updateAdminClock();
  });
})();
</script>
<script>
(function () {
  if (window.__itcAdminNotificationReady) return;
  window.__itcAdminNotificationReady = true;

  const toggle = document.getElementById('admin-notification-toggle');
  const panel = document.getElementById('admin-notification-panel');
  const list = document.getElementById('admin-notification-list');
  const markAllButton = document.getElementById('admin-mark-all-read');
  const csrfToken = @json(csrf_token());
  let lastNotifId = {{ (int) $lastAdminNotificationId }};
  let pollInFlight = false;

  function escapeHtml(value) {
    const node = document.createElement('div');
    node.textContent = value == null ? '' : String(value);
    return node.innerHTML;
  }

  function setPanelOpen(open) {
    if (!panel || !toggle) return;
    panel.classList.toggle('open', open);
    panel.setAttribute('aria-hidden', open ? 'false' : 'true');
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
  }

  function updateAdminNotificationBadge(count) {
    const value = Number(count || 0);
    document.querySelectorAll('[data-admin-notif-badge]').forEach(badge => {
      if (value > 0) {
        badge.textContent = value > 99 ? '99+' : value;
        badge.style.display = 'inline-flex';
      } else {
        badge.style.display = 'none';
      }
    });
  }

  function renderRecentNotifications(notifications) {
    if (!list) return;
    if (!Array.isArray(notifications) || notifications.length === 0) {
      list.innerHTML = `
        <div class="floating-notification-empty">
          <i class="fa-regular fa-bell-slash"></i>
          <strong>No notifications yet</strong>
          <span>New service activity will appear here.</span>
        </div>`;
      return;
    }

    list.innerHTML = notifications.map(notification => `
      <a href="${escapeHtml(notification.action_url)}"
         class="floating-notification-item ${notification.is_read ? 'is-read' : 'is-unread'}"
         data-admin-notification-item
         data-mark-read-url="${escapeHtml(notification.mark_read_url)}">
        <span class="floating-notification-icon">
          <i class="fa-solid ${escapeHtml(notification.icon || 'fa-bell')}"></i>
        </span>
        <span class="floating-notification-copy">
          <span class="floating-notification-title">${escapeHtml(notification.title)}</span>
          <span class="floating-notification-message">${escapeHtml(notification.message)}</span>
          <span class="floating-notification-time">${escapeHtml(notification.created_at)}</span>
        </span>
        ${notification.is_read ? '' : '<span class="floating-notification-dot"></span>'}
      </a>`).join('');

    list.querySelectorAll('[data-admin-notification-item]').forEach(item => {
      item.addEventListener('click', event => {
        if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || event.button !== 0) return;
        event.preventDefault();
        const destination = item.href;
        fetch(item.dataset.markReadUrl, {
          method: 'POST',
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken
          },
          credentials: 'same-origin'
        }).finally(() => window.location.href = destination);
      });
    });
  }

  function playNotifSound() {
    try {
      const context = new (window.AudioContext || window.webkitAudioContext)();
      [880, 1100, 880].forEach((frequency, index) => {
        const oscillator = context.createOscillator();
        const gain = context.createGain();
        oscillator.connect(gain);
        gain.connect(context.destination);
        oscillator.type = 'sine';
        oscillator.frequency.value = frequency;
        gain.gain.setValueAtTime(0.25, context.currentTime + index * 0.15);
        gain.gain.exponentialRampToValueAtTime(0.001, context.currentTime + index * 0.15 + 0.12);
        oscillator.start(context.currentTime + index * 0.15);
        oscillator.stop(context.currentTime + index * 0.15 + 0.15);
      });
    } catch (error) {}
  }

  function showAdminToast(title, message, icon = 'fa-bell', type = 'info', actionUrl = null) {
    const container = document.getElementById('toast-container');
    if (!container) return;
    const toast = document.createElement(actionUrl ? 'a' : 'div');
    if (actionUrl) toast.href = actionUrl;
    toast.className = `admin-toast admin-toast-${type}`;
    toast.innerHTML = `
      <span class="admin-toast-icon"><i class="fa-solid ${escapeHtml(icon)}"></i></span>
      <span class="admin-toast-copy">
        <strong>${escapeHtml(title)}</strong>
        <span>${escapeHtml(message)}</span>
      </span>
      <button type="button" aria-label="Dismiss notification"><i class="fa-solid fa-xmark"></i></button>`;
    toast.querySelector('button').addEventListener('click', event => {
      event.preventDefault();
      event.stopPropagation();
      toast.remove();
    });
    container.appendChild(toast);
    setTimeout(() => {
      if (!toast.parentNode) return;
      toast.classList.add('is-leaving');
      setTimeout(() => toast.remove(), 300);
    }, 6500);
  }

  function pollNotifications() {
    if (pollInFlight) return;
    pollInFlight = true;
    fetch('{{ route('admin.notifications.poll') }}?last_id=' + lastNotifId, {
      headers: {'Accept': 'application/json'},
      credentials: 'same-origin',
      cache: 'no-store'
    })
      .then(response => response.ok ? response.json() : Promise.reject())
      .then(data => {
        updateAdminNotificationBadge(data.unread_count);
        renderRecentNotifications(data.recent_notifications || []);

        if (Array.isArray(data.notifications)) {
          data.notifications.forEach(notification => {
            playNotifSound();
            const toastType = ['session_expired', 'request_rejected'].includes(notification.type) ? 'warn' : 'info';
            showAdminToast(
              notification.title,
              notification.message,
              notification.icon || 'fa-bell',
              toastType,
              notification.action_url
            );
          });
        }

        if (data.last_id !== undefined) lastNotifId = Number(data.last_id || lastNotifId);
      })
      .catch(() => {})
      .finally(() => { pollInFlight = false; });
  }

  toggle?.addEventListener('click', event => {
    if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || event.button !== 0) return;
    event.preventDefault();
    const willOpen = !panel.classList.contains('open');
    setPanelOpen(willOpen);
    if (willOpen) pollNotifications();
  });

  markAllButton?.addEventListener('click', () => {
    fetch('{{ route('admin.notifications.mark-all-read') }}', {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrfToken
      },
      credentials: 'same-origin'
    }).then(() => {
      updateAdminNotificationBadge(0);
      pollNotifications();
    }).catch(() => {});
  });

  document.addEventListener('click', event => {
    if (!panel?.classList.contains('open')) return;
    if (panel.contains(event.target) || toggle?.contains(event.target)) return;
    setPanelOpen(false);
  });

  document.addEventListener('keydown', event => {
    if (event.key === 'Escape') setPanelOpen(false);
  });

  setTimeout(pollNotifications, 800);
  setInterval(() => { if (!document.hidden) pollNotifications(); }, 4000);
  document.addEventListener('visibilitychange', () => {
    if (!document.hidden) pollNotifications();
  });

  function pollMessageBadge() {
    fetch('{{ route('admin.messages.unread-count') }}', {headers: {'Accept': 'application/json'}, cache: 'no-store'})
      .then(response => response.json())
      .then(data => {
        const badge = document.getElementById('sb-msg-badge');
        if (!badge) return;
        if (data.count > 0) {
          badge.textContent = data.count;
          badge.style.display = 'inline-block';
        } else {
          badge.style.display = 'none';
        }
      })
      .catch(() => {});
  }
  setInterval(pollMessageBadge, 8000);
})();
</script>
@endpush
