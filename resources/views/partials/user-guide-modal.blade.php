@php
  $__infographicDirectory = public_path('images/infographics');
  $__infographicFiles = collect();

  if (is_dir($__infographicDirectory)) {
      $__infographicFiles = collect(\Illuminate\Support\Facades\File::files($__infographicDirectory))
          ->filter(fn ($file) => in_array(strtolower($file->getExtension()), ['png', 'jpg', 'jpeg', 'webp'], true))
          ->sort(fn ($a, $b) => strnatcasecmp($a->getFilename(), $b->getFilename()))
          ->values();
  }
@endphp

<div class="modal-bg" id="userGuideModal">
  <div class="modal-box guide-modal-box">
    <div class="modal-hd">
      <h3>
        <img src="{{ asset('images/icons/user-guide_icon.png') }}" alt="" class="guide-title-icon">
        User Manual &amp; Guide
      </h3>
      <button type="button" class="modal-close" onclick="closeModal('userGuideModal')" aria-label="Close guide">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <div class="guide-reminder">
      <i class="fa-solid fa-circle-exclamation"></i>
      <div>
        <strong>Please review the User Manual or Infographics before using the IT Center Services.</strong>
        These guides help you upload the correct file, enter accurate request details, and avoid delays, rejected requests, or incorrect printing output.
      </div>
    </div>

    <div class="guide-layout">
      <aside class="guide-sidebar" aria-label="Guide sections">
        <button type="button" class="guide-nav-btn active" data-guide-target="manual" onclick="showGuideSection('manual')">
          <span class="guide-nav-icon"><i class="fa-solid fa-book-open"></i></span>
          <span>
            <strong>User Manual</strong>
            <small>Complete PDF guide</small>
          </span>
        </button>

        <button type="button" class="guide-nav-btn" data-guide-target="infographics" onclick="showGuideSection('infographics')">
          <span class="guide-nav-icon"><i class="fa-solid fa-images"></i></span>
          <span>
            <strong>Infographics</strong>
            <small>{{ $__infographicFiles->count() }} visual guide{{ $__infographicFiles->count() === 1 ? '' : 's' }}</small>
          </span>
        </button>

        <div class="guide-sidebar-note">
          <i class="fa-solid fa-lightbulb"></i>
          <span>Use the step-by-step visual guides when submitting a request for the first time.</span>
        </div>
      </aside>

      <section class="guide-content">
        <div class="guide-panel active" data-guide-panel="manual">
          <div class="guide-panel-heading">
            <div>
              <h4>User Manual</h4>
              <p>Read the complete instructions for account access, service requests, request tracking, and other system features.</p>
            </div>
          </div>
          <div class="guide-pdf-wrap">
            <iframe src="{{ asset('documents/user-manual.pdf') }}#view=FitH" title="IT Center Services User Manual"></iframe>
          </div>
        </div>

        <div class="guide-panel" data-guide-panel="infographics">
          <div class="guide-panel-heading">
            <div>
              <h4>Infographics</h4>
              <p>Browse the Canva visual guides and FAQs. Select an image to open its full-size version in a new tab.</p>
            </div>
          </div>

          @if($__infographicFiles->isNotEmpty())
            <div class="guide-infographic-grid">
              @foreach($__infographicFiles as $__guideFile)
                @php
                  $__guideFilename = $__guideFile->getFilename();
                  $__guideTitle = \Illuminate\Support\Str::of(pathinfo($__guideFilename, PATHINFO_FILENAME))
                      ->replace(['_', '—', '–'], ' ')
                      ->replaceMatches('/\s*-\s*/', ' ')
                      ->replaceMatches('/^\d+\s*/', '')
                      ->squish()
                      ->title();
                @endphp
                <a class="guide-infographic-card" href="{{ asset('images/infographics/'.$__guideFilename) }}" target="_blank" rel="noopener">
                  <div class="guide-image-wrap">
                    <img src="{{ asset('images/infographics/'.$__guideFilename) }}" alt="{{ $__guideTitle }}" loading="lazy">
                  </div>
                  <div class="guide-image-caption">
                    <span>{{ $__guideTitle }}</span>
                    <i class="fa-solid fa-up-right-from-square"></i>
                  </div>
                </a>
              @endforeach
            </div>
          @else
            <div class="guide-empty-state">
              <i class="fa-regular fa-images"></i>
              <h5>Infographics will appear here soon.</h5>
              <p>The IT Center is preparing visual step-by-step guides and FAQs for this section.</p>
            </div>
          @endif
        </div>
      </section>
    </div>

    <div class="modal-footer guide-footer">
      <a href="{{ asset('documents/user-manual.pdf') }}" target="_blank" rel="noopener" class="modal-btn secondary" id="guide-open-manual-link">
        <i class="fa-solid fa-up-right-from-square"></i> Open Manual in New Tab
      </a>
      <button type="button" class="modal-btn primary" onclick="closeModal('userGuideModal')">Close</button>
    </div>
  </div>
</div>

@push('styles')
<style>
.guide-modal-box{max-width:1180px!important;width:96vw!important;height:92vh!important;max-height:92vh!important;display:flex!important;flex-direction:column!important;overflow:hidden!important}
.guide-title-icon{width:18px;height:18px;vertical-align:middle;margin-right:7px;object-fit:contain}
.guide-reminder{margin:14px 16px 0;padding:12px 14px;border:1px solid #f5d47a;border-left:4px solid #f5a623;border-radius:10px;background:#fff8e1;color:#745000;display:flex;gap:10px;align-items:flex-start;font-size:.76rem;line-height:1.55;flex-shrink:0}
.guide-reminder>i{margin-top:2px;color:#e09100;font-size:.9rem}
.guide-layout{display:grid;grid-template-columns:235px minmax(0,1fr);gap:0;flex:1;min-height:0;padding:14px 16px 0}
.guide-sidebar{background:var(--gray100);border:1px solid var(--gray200);border-radius:12px 0 0 12px;padding:10px;display:flex;flex-direction:column;gap:8px;min-height:0}
.guide-nav-btn{width:100%;border:1.5px solid transparent;background:transparent;border-radius:10px;padding:11px;display:flex;gap:10px;align-items:center;text-align:left;color:var(--gray700);cursor:pointer;font-family:inherit;transition:.2s}
.guide-nav-btn:hover{background:var(--white);border-color:var(--gray200)}
.guide-nav-btn.active{background:var(--white);border-color:var(--g400);box-shadow:var(--shadow-sm);color:var(--g800)}
.guide-nav-icon{width:34px;height:34px;border-radius:9px;background:var(--g100);color:var(--g600);display:flex;align-items:center;justify-content:center;flex-shrink:0}
.guide-nav-btn strong{display:block;font-size:.78rem}.guide-nav-btn small{display:block;font-size:.64rem;color:var(--gray400);margin-top:2px}
.guide-sidebar-note{margin-top:auto;background:var(--g100);border-radius:9px;padding:10px;color:var(--g800);font-size:.66rem;line-height:1.5;display:flex;gap:7px;align-items:flex-start}
.guide-sidebar-note i{margin-top:2px;color:var(--g600)}
.guide-content{border:1px solid var(--gray200);border-left:none;border-radius:0 12px 12px 0;background:var(--white);min-width:0;min-height:0;overflow:hidden}
.guide-panel{display:none;height:100%;min-height:0;flex-direction:column}.guide-panel.active{display:flex}
.guide-panel-heading{padding:14px 17px;border-bottom:1px solid var(--gray100);flex-shrink:0}.guide-panel-heading h4{font-size:.9rem;color:var(--gray800);margin-bottom:3px}.guide-panel-heading p{font-size:.69rem;color:var(--gray600);line-height:1.5}
.guide-pdf-wrap{flex:1;min-height:0;background:var(--gray100)}.guide-pdf-wrap iframe{width:100%;height:100%;border:0;display:block}
.guide-infographic-grid{padding:16px;display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;overflow-y:auto;align-content:start}
.guide-infographic-card{border:1.5px solid var(--gray200);border-radius:12px;background:var(--white);overflow:hidden;text-decoration:none;box-shadow:var(--shadow-sm);transition:.2s;min-width:0}
.guide-infographic-card:hover{transform:translateY(-2px);border-color:var(--g400);box-shadow:var(--shadow-md)}
.guide-image-wrap{background:var(--gray100);height:360px;display:flex;align-items:flex-start;justify-content:center;overflow:auto;padding:8px}
.guide-image-wrap img{width:100%;height:auto;display:block;border-radius:7px}
.guide-image-caption{padding:10px 12px;display:flex;align-items:center;justify-content:space-between;gap:8px;color:var(--gray800);font-size:.72rem;font-weight:700}.guide-image-caption i{color:var(--g600);flex-shrink:0}
.guide-empty-state{height:100%;min-height:260px;display:flex;align-items:center;justify-content:center;flex-direction:column;text-align:center;padding:30px;color:var(--gray400)}
.guide-empty-state>i{font-size:2rem;margin-bottom:10px}.guide-empty-state h5{font-size:.86rem;color:var(--gray700);margin-bottom:5px}.guide-empty-state p{font-size:.72rem;max-width:360px;line-height:1.5}
.guide-footer{flex-shrink:0;justify-content:space-between}
.guide-footer a{text-decoration:none;display:inline-flex;align-items:center;gap:6px}
@media(max-width:768px){.guide-modal-box{width:98vw!important;height:94vh!important;max-height:94vh!important}.guide-reminder{margin:10px 10px 0}.guide-layout{grid-template-columns:1fr;padding:10px 10px 0}.guide-sidebar{border-radius:10px 10px 0 0;display:grid;grid-template-columns:1fr 1fr}.guide-sidebar-note{display:none}.guide-content{border-left:1px solid var(--gray200);border-radius:0 0 10px 10px}.guide-infographic-grid{grid-template-columns:1fr}.guide-image-wrap{height:auto;max-height:none}.guide-footer{gap:8px}.guide-footer .modal-btn{font-size:.68rem;padding:8px 10px}}
</style>
@endpush

@push('scripts')
<script>
window.showGuideSection = function(section) {
  document.querySelectorAll('#userGuideModal [data-guide-target]').forEach(button => {
    button.classList.toggle('active', button.dataset.guideTarget === section);
  });
  document.querySelectorAll('#userGuideModal [data-guide-panel]').forEach(panel => {
    panel.classList.toggle('active', panel.dataset.guidePanel === section);
  });
  const manualLink = document.getElementById('guide-open-manual-link');
  if (manualLink) manualLink.style.display = section === 'manual' ? 'inline-flex' : 'none';
};

window.openUserGuide = function(section = 'manual') {
  showGuideSection(section);
  openModal('userGuideModal');

  document.querySelectorAll('[data-first-guide-note]').forEach(note => note.remove());

  @auth
  if (!window._userGuideMarkedSeen) {
    window._userGuideMarkedSeen = true;
    fetch('{{ route('user-guide.seen') }}', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({opened: true})
    }).catch(() => { window._userGuideMarkedSeen = false; });
  }
  @endauth
};
</script>
@endpush
