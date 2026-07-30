@php
  $__sysVersion = \App\Models\Setting::get('system_version', '1.0.0');
  $__sysNotes   = \App\Models\Setting::get('system_version_notes', '');
@endphp
<div class="modal-bg" id="versionModal">
  <div class="modal-box" style="max-width:440px">
    <div class="modal-hd">
      <h3><i class="fa-solid fa-rocket" style="color:var(--g600);margin-right:7px"></i>What's New — v{{ $__sysVersion }}</h3>
      <button class="modal-close" onclick="closeVersionModal()"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body">
      @if(trim($__sysNotes))
        <div style="font-size:.82rem;color:var(--gray700);line-height:1.7;white-space:pre-line">{{ $__sysNotes }}</div>
      @else
        <div class="abox info"><i class="fa-solid fa-circle-info"></i><div>No changelog notes were provided for this version.</div></div>
      @endif
    </div>
    <div class="modal-footer" style="justify-content:center">
      <button type="button" class="modal-btn primary" onclick="closeVersionModal()"><i class="fa-solid fa-check"></i> Got it</button>
    </div>
  </div>
</div>
<script>
(function(){
  window.closeVersionModal = function(){
    const m = document.getElementById('versionModal');
    if (m) m.classList.remove('open');
  };
  
  const SESSION_KEY = 'itc_version_modal_shown_' + '{{ $__sysVersion }}';
  const m = document.getElementById('versionModal');
  if (m && !sessionStorage.getItem(SESSION_KEY)) {
    m.classList.add('open');
    sessionStorage.setItem(SESSION_KEY, '1');
  }
})();
</script>