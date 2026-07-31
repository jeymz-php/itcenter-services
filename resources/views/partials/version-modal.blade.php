@php
  $__sysVersion = \App\Models\Setting::get('system_version', '1.0.0');
  $__sysNotes = (string) \App\Models\Setting::get('system_version_notes', '');
  $__sysUpdatedAt = \App\Models\Setting::get('system_version_updated_at');
  $__versionHistory = collect(\App\Models\Setting::versionHistory());

  if (!$__versionHistory->contains(fn ($entry) => ($entry['version'] ?? null) === $__sysVersion)) {
      $__versionHistory->prepend([
          'version' => $__sysVersion,
          'notes' => $__sysNotes,
          'released_at' => $__sysUpdatedAt,
          'updated_at' => $__sysUpdatedAt,
          'updated_by' => null,
      ]);
  }

  $__versionHistory = $__versionHistory
      ->sortByDesc(fn ($entry) => $entry['released_at'] ?? $entry['updated_at'] ?? '')
      ->values();
  $__currentVersionEntry = $__versionHistory->firstWhere('version', $__sysVersion) ?? [
      'version' => $__sysVersion,
      'notes' => $__sysNotes,
      'released_at' => $__sysUpdatedAt,
  ];
  $__previousVersions = $__versionHistory
      ->reject(fn ($entry) => ($entry['version'] ?? null) === $__sysVersion)
      ->values();

  $__formatVersionDate = static function ($date) {
      if (!$date) return null;
      try {
          return \Carbon\Carbon::parse($date)->format('F j, Y · g:i A');
      } catch (\Throwable $e) {
          return null;
      }
  };
@endphp

<div class="modal-bg" id="versionModal" role="dialog" aria-modal="true" aria-labelledby="version-modal-title">
  <div class="modal-box version-modal-box">
    <div class="modal-hd">
      <h3 id="version-modal-title">
        <i class="fa-solid fa-rocket version-title-icon"></i>
        Version &amp; Update Notice
      </h3>
      <button type="button" class="modal-close" onclick="closeVersionModal()" aria-label="Close version notice">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <div class="modal-body version-modal-body">
      <section class="version-current-card">
        <div class="version-current-heading">
          <div>
            <span class="version-current-label">Current Version</span>
            <h4>v{{ $__sysVersion }}</h4>
          </div>
          <span class="version-current-badge"><i class="fa-solid fa-circle-check"></i> Latest</span>
        </div>

        @if($__formatVersionDate($__currentVersionEntry['released_at'] ?? $__sysUpdatedAt))
          <div class="version-date">
            <i class="fa-regular fa-calendar"></i>
            Released {{ $__formatVersionDate($__currentVersionEntry['released_at'] ?? $__sysUpdatedAt) }}
          </div>
        @endif

        @if(trim((string) ($__currentVersionEntry['notes'] ?? $__sysNotes)))
          <div class="version-notes">{{ $__currentVersionEntry['notes'] ?? $__sysNotes }}</div>
        @else
          <div class="version-empty-note">
            <i class="fa-solid fa-circle-info"></i>
            No update notes were provided for this version.
          </div>
        @endif
      </section>

      <section class="version-history-section">
        <div class="version-history-heading">
          <div>
            <h4>Previous Version Log</h4>
            <p>Review earlier releases and the changes recorded for each update.</p>
          </div>
          <span class="version-count">{{ $__previousVersions->count() }} previous</span>
        </div>

        @if($__previousVersions->isNotEmpty())
          <div class="version-timeline">
            @foreach($__previousVersions as $__index => $__entry)
              @php
                $__entryVersion = (string) ($__entry['version'] ?? 'Unknown');
                $__entryDate = $__formatVersionDate($__entry['released_at'] ?? $__entry['updated_at'] ?? null);
                $__entryNotes = trim((string) ($__entry['notes'] ?? ''));
              @endphp
              <details class="version-history-item" {{ $__index === 0 ? 'open' : '' }}>
                <summary>
                  <span class="version-dot"></span>
                  <span class="version-summary-main">
                    <strong>v{{ $__entryVersion }}</strong>
                    <small>{{ $__entryDate ?: 'Release date not recorded' }}</small>
                  </span>
                  <i class="fa-solid fa-chevron-down version-chevron"></i>
                </summary>
                <div class="version-history-content">
                  @if($__entryNotes)
                    <div class="version-notes">{{ $__entryNotes }}</div>
                  @else
                    <div class="version-empty-note compact">
                      <i class="fa-solid fa-circle-info"></i>
                      No update notes were recorded for this version.
                    </div>
                  @endif
                </div>
              </details>
            @endforeach
          </div>
        @else
          <div class="version-history-empty">
            <i class="fa-solid fa-clock-rotate-left"></i>
            <div>
              <strong>No previous versions recorded yet.</strong>
              <p>The current version is now registered. Future version updates saved in Admin Settings will automatically remain in this log.</p>
            </div>
          </div>
        @endif
      </section>
    </div>

    <div class="modal-footer version-modal-footer">
      <span><i class="fa-solid fa-shield-halved"></i> IT Center Services System</span>
      <button type="button" class="modal-btn primary" onclick="closeVersionModal()">
        <i class="fa-solid fa-check"></i> Got it
      </button>
    </div>
  </div>
</div>

@push('styles')
<style>
.version-modal-box{max-width:680px!important;width:min(94vw,680px)!important;max-height:90vh!important;display:flex!important;flex-direction:column!important;overflow:hidden!important}
.version-title-icon{color:var(--g600);margin-right:7px}.version-modal-body{overflow-y:auto!important;padding:16px!important;background:var(--gray100)}
.version-current-card{background:linear-gradient(145deg,var(--g800),var(--g600));border-radius:14px;padding:18px;color:#fff;box-shadow:var(--shadow-md)}
.version-current-heading{display:flex;align-items:flex-start;justify-content:space-between;gap:12px}.version-current-label{display:block;text-transform:uppercase;letter-spacing:.08em;font-size:.6rem;font-weight:800;color:rgba(255,255,255,.7);margin-bottom:2px}.version-current-heading h4{font-size:1.4rem;line-height:1.1;color:#fff}.version-current-badge{background:rgba(255,255,255,.16);border:1px solid rgba(255,255,255,.25);border-radius:999px;padding:6px 9px;font-size:.65rem;font-weight:800;white-space:nowrap}.version-date{font-size:.66rem;color:rgba(255,255,255,.72);margin-top:8px;display:flex;align-items:center;gap:6px}.version-notes{margin-top:13px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.16);border-radius:10px;padding:12px 13px;white-space:pre-line;font-size:.75rem;line-height:1.7;color:inherit}.version-empty-note{margin-top:13px;background:rgba(255,255,255,.12);border-radius:9px;padding:10px 12px;font-size:.72rem;line-height:1.55;display:flex;gap:8px;align-items:flex-start}.version-empty-note.compact{margin-top:0;background:var(--gray100);color:var(--gray600);border:1px solid var(--gray200)}
.version-history-section{margin-top:15px;background:var(--white);border:1px solid var(--gray200);border-radius:14px;padding:15px}.version-history-heading{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:12px}.version-history-heading h4{font-size:.85rem;color:var(--gray800);margin-bottom:3px}.version-history-heading p{font-size:.66rem;color:var(--gray400);line-height:1.45}.version-count{background:var(--g100);color:var(--g700);border-radius:999px;padding:5px 8px;font-size:.61rem;font-weight:800;white-space:nowrap}
.version-timeline{display:flex;flex-direction:column;gap:8px}.version-history-item{border:1px solid var(--gray200);border-radius:10px;overflow:hidden;background:var(--white)}.version-history-item summary{list-style:none;display:flex;align-items:center;gap:10px;padding:11px 12px;cursor:pointer;user-select:none}.version-history-item summary::-webkit-details-marker{display:none}.version-history-item[open]{border-color:var(--g300)}.version-history-item[open] summary{background:var(--g100)}.version-dot{width:9px;height:9px;border-radius:50%;background:var(--g500);box-shadow:0 0 0 4px var(--g100);flex-shrink:0}.version-summary-main{display:flex;flex-direction:column;gap:2px;flex:1;min-width:0}.version-summary-main strong{font-size:.76rem;color:var(--gray800)}.version-summary-main small{font-size:.61rem;color:var(--gray400)}.version-chevron{font-size:.65rem;color:var(--gray400);transition:.2s}.version-history-item[open] .version-chevron{transform:rotate(180deg);color:var(--g600)}.version-history-content{padding:0 12px 12px 31px;color:var(--gray700)}.version-history-content .version-notes{background:var(--gray100);border-color:var(--gray200);margin-top:0;color:var(--gray700)}
.version-history-empty{display:flex;align-items:flex-start;gap:11px;padding:13px;border:1px dashed var(--gray300);border-radius:10px;color:var(--gray400);background:var(--gray100)}.version-history-empty>i{font-size:1rem;color:var(--g500);margin-top:2px}.version-history-empty strong{display:block;font-size:.73rem;color:var(--gray700);margin-bottom:3px}.version-history-empty p{font-size:.65rem;line-height:1.5}.version-modal-footer{justify-content:space-between!important}.version-modal-footer>span{font-size:.62rem;color:var(--gray400);display:flex;align-items:center;gap:5px}
@media(max-width:560px){.version-modal-box{width:97vw!important;max-height:94vh!important}.version-current-card{padding:15px}.version-history-heading{flex-direction:column}.version-history-content{padding-left:12px}.version-modal-footer>span{display:none}.version-modal-footer{justify-content:flex-end!important}}
</style>
@endpush

@push('scripts')
<script>
(function(){
  window.closeVersionModal = function(){
    const modal = document.getElementById('versionModal');
    if (modal) modal.classList.remove('open');
  };

  const showCurrentVersionNoticeOnce = function(){
    const modal = document.getElementById('versionModal');
    if (!modal) return;

    const sessionKey = 'itc_version_modal_shown_' + @json($__sysVersion);
    try {
      if (!sessionStorage.getItem(sessionKey)) {
        modal.classList.add('open');
        sessionStorage.setItem(sessionKey, '1');
      }
    } catch (error) {
      // Storage can be unavailable in strict/private browser modes. The modal
      // remains accessible through the version link in the user sidebar.
    }
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', showCurrentVersionNoticeOnce, { once: true });
  } else {
    showCurrentVersionNoticeOnce();
  }
})();
</script>
@endpush
