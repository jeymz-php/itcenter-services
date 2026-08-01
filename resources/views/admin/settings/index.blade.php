@extends('layouts.app')
@section('title','Settings | Admin')
@section('body-class','dash-page')
@section('content')
<div class="dash-wrap">
  @include('admin.partials.sidebar')
  <main class="main">
    @include('admin.partials.topbar', [
      'title'=>'System Settings',
      'sub'=>$isSuperAdmin ? 'Global configuration and service controls' : 'Service availability and system release history'
    ])
    <div class="content">

      @if(session('success'))
        <div class="abox ok" style="margin-bottom:16px"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
      @endif
      @if($errors->any())
        <div class="abox err" style="margin-bottom:16px"><i class="fa-solid fa-triangle-exclamation"></i> {{ $errors->first() }}</div>
      @endif

      {{-- SERVICE AVAILABILITY: accessible to both Admin and Super Admin --}}
      <div class="profile-card" style="margin-bottom:16px">
        <div class="profile-card-hd">
          <i class="fa-solid fa-toggle-on"></i> Service Availability
          <span class="tag tag-active" style="margin-left:auto">GLOBAL</span>
        </div>
        <div class="profile-card-body">
          <div class="abox warn" style="margin-bottom:16px">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <div>
              Turning a service <strong>OFF</strong> immediately prevents new requests from the Student/Faculty portal, Public Request page, and mobile/API access. Existing requests remain available for processing and history.
            </div>
          </div>

          <form action="{{ route('admin.settings.services') }}" method="POST" id="service-availability-form">
            @csrf
            @method('PUT')
            <div class="service-toggle-grid">
              @foreach([
                'printing'  => ['Printing','fa-print','var(--blue)','var(--blue-bg)','Document and photo printing requests'],
                'photocopy' => ['Photocopy','fa-copy','var(--orange)','var(--orange-bg)','Document photocopy requests'],
                'research'  => ['Research / PC Lab','fa-desktop','var(--g600)','var(--g100)','Computer laboratory and research sessions'],
              ] as $service => [$label,$icon,$color,$bg,$description])
                @php $enabled = $serviceAvailability[$service] ?? true; @endphp
                <label class="service-toggle-card {{ $enabled ? 'enabled' : 'disabled' }}" data-service-card="{{ $service }}">
                  <div class="service-toggle-icon" style="background:{{ $bg }};color:{{ $color }}"><i class="fa-solid {{ $icon }}"></i></div>
                  <div class="service-toggle-copy">
                    <div class="service-toggle-title">{{ $label }}</div>
                    <div class="service-toggle-desc">{{ $description }}</div>
                    <div class="service-toggle-state" data-service-state="{{ $service }}" style="color:{{ $enabled ? 'var(--g600)' : 'var(--red)' }}">
                      <i class="fa-solid {{ $enabled ? 'fa-circle-check' : 'fa-circle-xmark' }}"></i>
                      {{ $enabled ? 'Available to users' : 'Currently unavailable' }}
                    </div>
                  </div>
                  <span class="switch-control">
                    <input type="checkbox" name="service_{{ $service }}_enabled" value="1" {{ $enabled ? 'checked' : '' }} data-service-toggle="{{ $service }}">
                    <span class="switch-slider"></span>
                  </span>
                </label>
              @endforeach
            </div>

            <div style="display:flex;justify-content:flex-end;margin-top:16px">
              <button type="submit" class="btn" style="max-width:260px">
                <i class="fa-solid fa-floppy-disk"></i> Save Service Availability
              </button>
            </div>
          </form>
        </div>
      </div>

      @if($isSuperAdmin)
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;align-items:start">

        <div class="profile-card">
          <div class="profile-card-hd"><i class="fa-solid fa-gauge-high"></i> Daily Usage Limits</div>
          <div class="profile-card-body">
            <div class="abox info" style="margin-bottom:16px">
              <i class="fa-solid fa-circle-info"></i>
              <div>Applies to students and faculty/staff. Resets at 12:00 AM (Asia/Manila).</div>
            </div>
            <form action="{{ route('admin.settings.update') }}" method="POST">
              @csrf @method('PUT')
              <div class="fg">
                <div class="flabel"><i class="fa-solid fa-print" style="color:var(--blue)"></i> Daily Printing Limit (pages/sheets)</div>
                <input type="number" name="daily_printing_page_limit" class="fc" min="1" max="1000" value="{{ old('daily_printing_page_limit', $dailyPrintingLimit) }}" required>
              </div>
              <div class="fg">
                <div class="flabel"><i class="fa-solid fa-copy" style="color:var(--orange)"></i> Daily Photocopy Limit (pages/sheets)</div>
                <input type="number" name="daily_photocopy_page_limit" class="fc" min="1" max="1000" value="{{ old('daily_photocopy_page_limit', $dailyPhotocopyLimit) }}" required>
              </div>
              <div class="fg">
                <div class="flabel"><i class="fa-solid fa-desktop" style="color:var(--g600)"></i> Daily Research/PC-Lab Limit (minutes)</div>
                <input type="number" name="daily_research_minutes" class="fc" min="1" max="1440" value="{{ old('daily_research_minutes', $dailyResearchLimit) }}" required>
              </div>
              <button type="submit" class="btn"><i class="fa-solid fa-floppy-disk"></i> Save Limits</button>
            </form>
          </div>
        </div>

        <div class="profile-card" style="grid-column:1/-1">
          <div class="profile-card-hd">
            <i class="fa-solid fa-calendar-days"></i> IT Center Operating Schedule
            <span class="tag {{ \App\Models\Setting::isWithinSystemHours() ? 'tag-active' : 'tag-rej' }}" style="margin-left:auto">
              {{ \App\Models\Setting::isWithinSystemHours() ? 'OPEN NOW' : 'CLOSED NOW' }}
            </span>
          </div>
          <div class="profile-card-body">
            <div class="abox info" style="margin-bottom:16px">
              <i class="fa-solid fa-circle-info"></i>
              <div>
                <strong>Default schedule:</strong> Monday to Thursday and Saturday, 7:00 AM to 6:30 PM. Friday and Sunday are closed by default, but a Super Admin may manually open either day and change its hours.
                Users may still browse while closed, but new requests are blocked on the web portal, Public Request page, and mobile/API.
              </div>
            </div>

            <form action="{{ route('admin.settings.hours') }}" method="POST" id="operating-schedule-form" data-loading-message="Saving operating schedule...">
              @csrf @method('PUT')
              <div class="schedule-grid">
                @foreach($operatingSchedule as $day => $entry)
                  @php
                    $enabledValue = old("operating_schedule.$day.enabled", $entry['enabled'] ? '1' : '0');
                    $enabled = in_array((string)$enabledValue, ['1','true','on'], true);
                  @endphp
                  <div class="schedule-day-row {{ $enabled ? 'schedule-open' : 'schedule-closed' }}" data-schedule-row="{{ $day }}">
                    <div class="schedule-day-name">
                      <div class="schedule-day-icon"><i class="fa-solid {{ $enabled ? 'fa-door-open' : 'fa-door-closed' }}"></i></div>
                      <div>
                        <strong>{{ ucfirst($day) }}</strong>
                        <span data-schedule-status="{{ $day }}">{{ $enabled ? 'Open for requests' : 'Closed all day' }}</span>
                      </div>
                    </div>

                    <label class="schedule-switch" title="Open or close {{ ucfirst($day) }}">
                      <input type="hidden" name="operating_schedule[{{ $day }}][enabled]" value="0">
                      <input type="checkbox" name="operating_schedule[{{ $day }}][enabled]" value="1" {{ $enabled ? 'checked' : '' }} data-schedule-toggle="{{ $day }}">
                      <span></span>
                    </label>

                    <div class="schedule-time-field">
                      <label>Opens</label>
                      <input type="time" class="fc" name="operating_schedule[{{ $day }}][open]" value="{{ old("operating_schedule.$day.open", $entry['open']) }}" required>
                    </div>
                    <div class="schedule-time-separator"><i class="fa-solid fa-arrow-right"></i></div>
                    <div class="schedule-time-field">
                      <label>Closes</label>
                      <input type="time" class="fc" name="operating_schedule[{{ $day }}][close]" value="{{ old("operating_schedule.$day.close", $entry['close']) }}" required>
                    </div>
                  </div>
                @endforeach
              </div>

              <div style="display:flex;gap:9px;justify-content:flex-end;flex-wrap:wrap;margin-top:16px">
                <button type="button" class="btn-outline" id="reset-default-schedule"><i class="fa-solid fa-rotate-left"></i> Restore Default Schedule</button>
                <button type="submit" class="btn" style="max-width:280px"><i class="fa-solid fa-floppy-disk"></i> Save Operating Schedule</button>
              </div>
            </form>
          </div>
        </div>

        <div class="profile-card">
          <div class="profile-card-hd"><i class="fa-solid fa-rocket"></i> Version &amp; Update Notice</div>
          <div class="profile-card-body">
            <div class="abox info" style="margin-bottom:16px">
              <i class="fa-solid fa-circle-info"></i>
              <div>Each saved version is preserved below. When the version number changes, users see a one-time Version & Update Notice when they open the user portal.</div>
            </div>
            <form action="{{ route('admin.settings.version') }}" method="POST">
              @csrf @method('PUT')
              <div class="fg">
                <div class="flabel"><i class="fa-solid fa-tag" style="color:var(--g600)"></i> Version Number</div>
                <div style="display:flex;gap:8px">
                  <input type="text" name="system_version" id="version-input" class="fc" value="{{ old('system_version', $systemVersion) }}" required style="flex:1">
                  <button type="button" class="btn-outline" onclick="autoGenerateVersion()" style="white-space:nowrap"><i class="fa-solid fa-wand-magic-sparkles"></i> Auto-Generate</button>
                </div>
              </div>
              <div class="fg">
                <div class="flabel"><i class="fa-solid fa-list-check" style="color:var(--g600)"></i> What’s New</div>
                <textarea name="system_version_notes" class="fc" rows="5" placeholder="- Added a new feature&#10;- Improved request processing&#10;- Fixed an issue" style="resize:vertical">{{ old('system_version_notes', $systemVersionNotes) }}</textarea>
              </div>
              <button type="submit" class="btn"><i class="fa-solid fa-floppy-disk"></i> Save Version &amp; Log</button>
            </form>
          </div>
        </div>

        <div class="profile-card" style="{{ $maintenanceMode ? 'border-color:var(--red)' : '' }}">
          <div class="profile-card-hd">
            <i class="fa-solid fa-screwdriver-wrench"></i> Maintenance Mode
            @if($maintenanceMode)<span class="tag tag-rej" style="margin-left:auto">CURRENTLY DOWN</span>@endif
          </div>
          <div class="profile-card-body">
            <div class="abox {{ $maintenanceMode ? 'err' : 'warn' }}" style="margin-bottom:16px">
              <i class="fa-solid fa-triangle-exclamation"></i>
              <div>Maintenance mode blocks the public site and all student/faculty pages. Admin access remains available.</div>
            </div>
            <form action="{{ route('admin.settings.maintenance') }}" method="POST">
              @csrf @method('PUT')
              <label class="cb-row" style="margin-bottom:14px">
                <input type="checkbox" name="system_maintenance_mode" value="1" {{ $maintenanceMode ? 'checked' : '' }}>
                <strong>Enable maintenance mode</strong>
              </label>
              <div class="fg">
                <div class="flabel">Message shown to users</div>
                <textarea name="system_maintenance_message" class="fc" rows="3" style="resize:vertical">{{ old('system_maintenance_message', $maintenanceMessage) }}</textarea>
              </div>
              <button type="submit" class="btn" style="{{ $maintenanceMode ? '' : 'background:linear-gradient(135deg,var(--red),#c62828)' }}">
                <i class="fa-solid fa-power-off"></i> {{ $maintenanceMode ? 'Update Maintenance Settings' : 'Save and Turn System Down' }}
              </button>
            </form>
          </div>
        </div>
      </div>
      @else
        <div class="abox info" style="margin-bottom:16px">
          <i class="fa-solid fa-user-shield"></i>
          <div>As an Admin, you can manage service availability. Daily limits, system hours, version publishing, and maintenance mode remain restricted to the Super Admin.</div>
        </div>
      @endif

      {{-- VERSION HISTORY: visible to both Admin and Super Admin --}}
      <div class="profile-card" style="margin-top:16px">
        <div class="profile-card-hd">
          <i class="fa-solid fa-clock-rotate-left"></i> Version History
          <span class="tag tag-active" style="margin-left:auto">{{ $versionHistory->count() }} RELEASE{{ $versionHistory->count() === 1 ? '' : 'S' }}</span>
        </div>
        <div class="profile-card-body">
          <div class="version-history-list">
            @forelse($versionHistory as $index => $entry)
              @php
                $isCurrent = ($entry['version'] ?? '') === $systemVersion;
                $releasedAt = !empty($entry['released_at']) ? \Carbon\Carbon::parse($entry['released_at']) : null;
              @endphp
              <details class="version-history-item" {{ $index === 0 ? 'open' : '' }}>
                <summary>
                  <span class="version-history-badge">v{{ $entry['version'] ?? 'Unknown' }}</span>
                  <span class="version-history-meta">
                    @if($isCurrent)<span class="tag tag-active">CURRENT</span>@endif
                    <span>{{ $releasedAt ? $releasedAt->format('M d, Y · g:i A') : 'Date not recorded' }}</span>
                  </span>
                  <i class="fa-solid fa-chevron-down"></i>
                </summary>
                <div class="version-history-notes">
                  @if(trim((string)($entry['notes'] ?? '')))
                    {!! nl2br(e($entry['notes'])) !!}
                  @else
                    <span style="color:var(--gray400)">No changelog notes were provided for this release.</span>
                  @endif
                  @if(!empty($entry['updated_by']))
                    <div class="version-updated-by"><i class="fa-solid fa-user-shield"></i> Published by {{ $entry['updated_by'] }}</div>
                  @endif
                </div>
              </details>
            @empty
              <div class="abox info"><i class="fa-solid fa-circle-info"></i><div>No version history has been recorded yet. Saving the current version will create the first entry.</div></div>
            @endforelse
          </div>
        </div>
      </div>

    </div>
  </main>
</div>

@push('styles')
<style>
.service-toggle-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}
.service-toggle-card{border:1.5px solid var(--gray200);border-radius:13px;padding:15px;display:flex;align-items:center;gap:12px;cursor:pointer;background:var(--white);transition:.2s;position:relative}
.service-toggle-card.enabled{border-color:var(--g300);background:var(--g50)}
.service-toggle-card.disabled{border-color:#ffc7c7;background:var(--red-bg)}
.service-toggle-card:hover{transform:translateY(-1px);box-shadow:var(--shadow-sm)}
.service-toggle-icon{width:42px;height:42px;border-radius:11px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:1rem}
.service-toggle-copy{flex:1;min-width:0}.service-toggle-title{font-size:.82rem;font-weight:800;color:var(--gray800)}.service-toggle-desc{font-size:.65rem;color:var(--gray400);line-height:1.4;margin-top:3px}.service-toggle-state{font-size:.67rem;font-weight:700;margin-top:7px}
.switch-control{position:relative;width:44px;height:24px;flex-shrink:0}.switch-control input{opacity:0;width:0;height:0}.switch-slider{position:absolute;inset:0;background:var(--gray300);border-radius:30px;transition:.2s}.switch-slider:before{content:'';position:absolute;width:18px;height:18px;left:3px;top:3px;background:#fff;border-radius:50%;box-shadow:0 1px 4px rgba(0,0,0,.2);transition:.2s}.switch-control input:checked+.switch-slider{background:var(--g500)}.switch-control input:checked+.switch-slider:before{transform:translateX(20px)}
.version-history-list{display:flex;flex-direction:column;gap:9px}.version-history-item{border:1.5px solid var(--gray200);border-radius:11px;background:var(--white);overflow:hidden}.version-history-item summary{list-style:none;cursor:pointer;padding:12px 14px;display:flex;align-items:center;gap:10px}.version-history-item summary::-webkit-details-marker{display:none}.version-history-badge{font-size:.76rem;font-weight:800;color:var(--g700);background:var(--g100);border-radius:8px;padding:5px 9px;white-space:nowrap}.version-history-meta{display:flex;align-items:center;gap:8px;flex:1;font-size:.68rem;color:var(--gray400)}.version-history-item summary>i{font-size:.68rem;color:var(--gray400);transition:.2s}.version-history-item[open] summary>i{transform:rotate(180deg)}.version-history-notes{border-top:1px solid var(--gray100);padding:13px 15px;font-size:.75rem;color:var(--gray700);line-height:1.65}.version-updated-by{margin-top:10px;font-size:.64rem;color:var(--gray400)}
.schedule-grid{display:flex;flex-direction:column;gap:9px}.schedule-day-row{display:grid;grid-template-columns:minmax(170px,1.3fr) 54px minmax(130px,1fr) 28px minmax(130px,1fr);align-items:center;gap:10px;border:1.5px solid var(--gray200);border-radius:12px;padding:11px 13px;background:var(--white);transition:.2s}.schedule-day-row.schedule-open{border-color:var(--g200);background:var(--g50)}.schedule-day-row.schedule-closed{background:var(--gray100);opacity:.82}.schedule-day-name{display:flex;align-items:center;gap:10px}.schedule-day-icon{width:36px;height:36px;border-radius:9px;background:var(--white);border:1px solid var(--gray200);display:flex;align-items:center;justify-content:center;color:var(--g600)}.schedule-closed .schedule-day-icon{color:var(--gray400)}.schedule-day-name strong{display:block;font-size:.78rem;color:var(--gray800)}.schedule-day-name span{display:block;font-size:.62rem;color:var(--gray400);margin-top:2px}.schedule-switch{position:relative;width:44px;height:24px;cursor:pointer}.schedule-switch input[type=checkbox]{opacity:0;width:0;height:0}.schedule-switch span{position:absolute;inset:0;border-radius:999px;background:var(--gray300);transition:.2s}.schedule-switch span:before{content:'';position:absolute;width:18px;height:18px;left:3px;top:3px;border-radius:50%;background:#fff;box-shadow:0 1px 4px rgba(0,0,0,.2);transition:.2s}.schedule-switch input:checked+span{background:var(--g500)}.schedule-switch input:checked+span:before{transform:translateX(20px)}.schedule-time-field label{display:block;font-size:.59rem;font-weight:800;color:var(--gray400);text-transform:uppercase;margin-bottom:4px}.schedule-time-field .fc{padding:8px 10px;background:var(--white)}.schedule-time-separator{text-align:center;color:var(--gray400);font-size:.7rem}
@media(max-width:900px){.service-toggle-grid{grid-template-columns:1fr}.content>div[style*="grid-template-columns:1fr 1fr"]{grid-template-columns:1fr!important}.schedule-day-row{grid-template-columns:1fr 50px}.schedule-time-field{grid-column:span 1}.schedule-time-separator{display:none}}
@media(max-width:560px){.schedule-day-row{grid-template-columns:1fr 48px}.schedule-time-field{grid-column:1/-1}.schedule-day-row .schedule-time-field+.schedule-time-field{margin-top:-4px}}
</style>
@endpush

@push('scripts')
<script>
function autoGenerateVersion(){
  const d = new Date();
  document.getElementById('version-input').value = d.getFullYear()+'.'+String(d.getMonth()+1).padStart(2,'0')+'.'+String(d.getDate()).padStart(2,'0');
}

document.querySelectorAll('[data-service-toggle]').forEach(toggle => {
  toggle.addEventListener('change', () => {
    const service = toggle.dataset.serviceToggle;
    const card = document.querySelector(`[data-service-card="${service}"]`);
    const state = document.querySelector(`[data-service-state="${service}"]`);
    card.classList.toggle('enabled', toggle.checked);
    card.classList.toggle('disabled', !toggle.checked);
    state.style.color = toggle.checked ? 'var(--g600)' : 'var(--red)';
    state.innerHTML = toggle.checked
      ? '<i class="fa-solid fa-circle-check"></i> Available to users'
      : '<i class="fa-solid fa-circle-xmark"></i> Currently unavailable';
  });
});

const defaultSchedule = @json(\App\Models\Setting::defaultOperatingSchedule());
function refreshScheduleRow(day, checked){
  const row = document.querySelector(`[data-schedule-row="${day}"]`);
  if(!row) return;
  row.classList.toggle('schedule-open', checked);
  row.classList.toggle('schedule-closed', !checked);
  const status = row.querySelector(`[data-schedule-status="${day}"]`);
  if(status) status.textContent = checked ? 'Open for requests' : 'Closed all day';
  const icon = row.querySelector('.schedule-day-icon i');
  if(icon) icon.className = checked ? 'fa-solid fa-door-open' : 'fa-solid fa-door-closed';
}

document.querySelectorAll('[data-schedule-toggle]').forEach(toggle => {
  toggle.addEventListener('change', () => refreshScheduleRow(toggle.dataset.scheduleToggle, toggle.checked));
});

document.getElementById('reset-default-schedule')?.addEventListener('click', () => {
  Object.entries(defaultSchedule).forEach(([day, entry]) => {
    const row = document.querySelector(`[data-schedule-row="${day}"]`);
    const toggle = row?.querySelector('[data-schedule-toggle]');
    const times = row?.querySelectorAll('input[type="time"]');
    if(toggle) toggle.checked = Boolean(entry.enabled);
    if(times?.[0]) times[0].value = entry.open;
    if(times?.[1]) times[1].value = entry.close;
    refreshScheduleRow(day, Boolean(entry.enabled));
  });
});
</script>
@endpush
@endsection
