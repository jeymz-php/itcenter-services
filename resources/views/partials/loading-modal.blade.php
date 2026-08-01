{{--
  Global loading feedback.
  It never delays navigation or form submission: the overlay appears only when
  the browser is genuinely still waiting after a short anti-flicker delay.
--}}
<div id="system-loading-modal" class="system-loading-modal" aria-hidden="true" role="status" aria-live="polite">
  <div class="system-loading-card">
    <div class="system-loading-animation" aria-hidden="true">
      <span></span><span></span><span></span>
      <i class="fa-solid fa-gears"></i>
    </div>
    <div class="system-loading-title" id="system-loading-title">Processing...</div>
    <div class="system-loading-subtitle">Please wait while the system completes your request.</div>
    <div class="system-loading-track" aria-hidden="true">
      <div class="system-loading-progress" id="system-loading-progress"></div>
    </div>
    <div class="system-loading-percent" id="system-loading-percent">0%</div>
  </div>
</div>

<style>
.system-loading-modal{
  position:fixed;inset:0;z-index:20000;display:flex;align-items:center;justify-content:center;
  padding:20px;background:rgba(7,31,22,.58);backdrop-filter:blur(5px);
  opacity:0;visibility:hidden;pointer-events:none;transition:opacity .16s ease,visibility .16s ease
}
.system-loading-modal.is-visible{opacity:1;visibility:visible;pointer-events:auto}
.system-loading-card{
  width:min(92vw,390px);background:#fff;border-radius:18px;padding:26px 24px 22px;text-align:center;
  box-shadow:0 20px 60px rgba(7,31,22,.28);transform:translateY(8px) scale(.985);
  transition:transform .18s ease;border:1px solid rgba(36,150,96,.14)
}
.system-loading-modal.is-visible .system-loading-card{transform:translateY(0) scale(1)}
.system-loading-animation{
  width:72px;height:72px;border-radius:22px;margin:0 auto 15px;position:relative;
  display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.45rem;
  background:linear-gradient(135deg,#18633f,#2db877);box-shadow:0 10px 24px rgba(30,125,79,.24)
}
.system-loading-animation i{animation:systemGearSpin 1.5s linear infinite}
.system-loading-animation span{position:absolute;width:7px;height:7px;border-radius:50%;background:#a8e8cc;animation:systemDotPulse 1.2s ease-in-out infinite}
.system-loading-animation span:nth-child(1){top:9px;left:15px;animation-delay:0s}
.system-loading-animation span:nth-child(2){top:8px;right:15px;animation-delay:.18s}
.system-loading-animation span:nth-child(3){bottom:10px;right:14px;animation-delay:.36s}
.system-loading-title{font-size:.98rem;font-weight:800;color:#1e3530;margin-bottom:5px}
.system-loading-subtitle{font-size:.73rem;line-height:1.55;color:#6f8d83;margin-bottom:17px}
.system-loading-track{height:9px;border-radius:999px;background:#e4eee9;overflow:hidden;position:relative}
.system-loading-progress{position:relative;overflow:hidden;height:100%;width:0;border-radius:999px;background:linear-gradient(90deg,#18633f,#2db877);transition:width .22s ease}
.system-loading-progress::after{content:'';position:absolute;inset:0;background:linear-gradient(100deg,transparent 20%,rgba(255,255,255,.55) 50%,transparent 80%);transform:translateX(-100%);animation:systemProgressShine 1.15s linear infinite}
.system-loading-percent{font-size:.67rem;font-weight:800;color:#1e7d4f;margin-top:7px;font-variant-numeric:tabular-nums}
@keyframes systemGearSpin{to{transform:rotate(360deg)}}
@keyframes systemDotPulse{0%,100%{opacity:.35;transform:scale(.8)}50%{opacity:1;transform:scale(1.25)}}
@keyframes systemProgressShine{to{transform:translateX(100%)}}
@media (prefers-reduced-motion:reduce){
  .system-loading-modal,.system-loading-card,.system-loading-progress{transition:none}
  .system-loading-animation i,.system-loading-animation span,.system-loading-progress::after{animation:none}
}
</style>

<script>
(function () {
  if (window.ITLoading) return;

  const modal = document.getElementById('system-loading-modal');
  const title = document.getElementById('system-loading-title');
  const bar = document.getElementById('system-loading-progress');
  const percent = document.getElementById('system-loading-percent');
  if (!modal || !title || !bar || !percent) return;

  let showTimer = null;
  let progressTimer = null;
  let progress = 0;
  let activeSubmitter = null;
  let originalButtonHtml = null;

  function paint(value) {
    progress = Math.max(0, Math.min(100, value));
    bar.style.width = progress.toFixed(0) + '%';
    percent.textContent = Math.round(progress) + '%';
  }

  function startProgress() {
    clearInterval(progressTimer);
    paint(10);
    progressTimer = setInterval(function () {
      if (progress >= 92) return;
      const step = Math.max(.7, (92 - progress) * .08);
      paint(Math.min(92, progress + step));
    }, 260);
  }

  function showNow(message) {
    clearTimeout(showTimer);
    title.textContent = message || 'Processing...';
    modal.classList.add('is-visible');
    modal.setAttribute('aria-hidden', 'false');
    document.documentElement.setAttribute('data-system-loading', 'true');
    startProgress();
  }

  function show(message, delay) {
    clearTimeout(showTimer);
    const wait = Number.isFinite(delay) ? delay : 140;
    showTimer = setTimeout(function () { showNow(message); }, Math.max(0, wait));
  }

  function update(value, message) {
    if (message) title.textContent = message;
    paint(value);
  }

  function restoreSubmitter() {
    if (!activeSubmitter) return;
    activeSubmitter.disabled = false;
    activeSubmitter.removeAttribute('aria-busy');
    if (originalButtonHtml !== null) activeSubmitter.innerHTML = originalButtonHtml;
    activeSubmitter = null;
    originalButtonHtml = null;
  }

  function hide() {
    clearTimeout(showTimer);
    clearInterval(progressTimer);
    modal.classList.remove('is-visible');
    modal.setAttribute('aria-hidden', 'true');
    document.documentElement.removeAttribute('data-system-loading');
    paint(0);
    restoreSubmitter();
  }

  function markSubmitter(button) {
    if (!button || button.matches('[data-keep-button-content]')) return;
    activeSubmitter = button;
    originalButtonHtml = button.innerHTML;
    button.setAttribute('aria-busy', 'true');
    button.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';
    // Delay disabling until the browser has collected the submitter name/value.
    setTimeout(function () { if (activeSubmitter === button) button.disabled = true; }, 0);
  }

  window.ITLoading = { show: show, showNow: showNow, update: update, hide: hide };

  // Window-level listeners run after page-specific document listeners, so a
  // prevented modal/AJAX action does not accidentally trigger the page loader.
  window.addEventListener('submit', function (event) {
    const form = event.target;
    if (!(form instanceof HTMLFormElement) || event.defaultPrevented || form.matches('[data-no-loading]')) return;

    const submitter = event.submitter || form.querySelector('[type="submit"]');
    markSubmitter(submitter);
    const message = submitter?.dataset.loadingMessage || form.dataset.loadingMessage || 'Processing your request...';
    show(message, 100);
  });

  window.addEventListener('click', function (event) {
    const link = event.target.closest && event.target.closest('a[href]');
    if (!link || event.defaultPrevented) return;
    if (link.matches('[data-no-loading], [download], [target="_blank"], .request-detail-link')) return;

    const rawHref = link.getAttribute('href') || '';
    if (!rawHref || rawHref.startsWith('#') || rawHref.startsWith('javascript:') || rawHref.startsWith('mailto:') || rawHref.startsWith('tel:')) return;

    let url;
    try { url = new URL(link.href, window.location.href); } catch (e) { return; }
    if (url.origin !== window.location.origin) return;
    if (url.pathname === window.location.pathname && url.search === window.location.search && url.hash) return;

    show(link.dataset.loadingMessage || 'Loading page...', 140);
  });

  window.addEventListener('pageshow', hide);
  window.addEventListener('beforeunload', function () {
    if (modal.classList.contains('is-visible')) paint(100);
  });
})();
</script>
