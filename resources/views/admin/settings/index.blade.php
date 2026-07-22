@extends('layouts.app')
@section('title','Settings | Admin')
@section('body-class','dash-page')
@section('content')
<div class="dash-wrap">
  @include('admin.partials.sidebar')
  <main class="main">
    @include('admin.partials.topbar', ['title'=>'System Settings','sub'=>'Site-wide configuration (super admin only)'])
    <div class="content">

      @if(session('success'))
        <div class="abox ok" style="margin-bottom:16px"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
      @endif
      @if($errors->any())
        <div class="abox err" style="margin-bottom:16px"><i class="fa-solid fa-triangle-exclamation"></i> {{ $errors->first() }}</div>
      @endif

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;align-items:start">

      <div class="profile-card">
        <div class="profile-card-hd"><i class="fa-solid fa-gauge-high"></i> Daily Usage Limits</div>
        <div class="profile-card-body">
          <div class="abox info" style="margin-bottom:16px">
            <i class="fa-solid fa-circle-info"></i>
            <div>Applies to students & faculty/staff only. Guest requests are not subject to these limits. Resets at 12:00 AM (Asia/Manila).</div>
          </div>

          <form action="{{ route('admin.settings.update') }}" method="POST">
            @csrf
            @method('PUT')
            <div class="fg">
              <div class="flabel"><i class="fa-solid fa-print" style="color:var(--blue)"></i> Daily Printing Limit (pages/sheets)</div>
              <input type="number" name="daily_printing_page_limit" class="fc" min="1" max="1000" value="{{ old('daily_printing_page_limit', $dailyPrintingLimit) }}" required>
              <div style="font-size:.7rem;color:var(--gray400);margin-top:4px">Total sheets (pages × copies) a student/faculty member can print per day.</div>
            </div>

            <div class="fg">
              <div class="flabel"><i class="fa-solid fa-copy" style="color:var(--orange)"></i> Daily Photocopy Limit (pages/sheets)</div>
              <input type="number" name="daily_photocopy_page_limit" class="fc" min="1" max="1000" value="{{ old('daily_photocopy_page_limit', $dailyPhotocopyLimit) }}" required>
              <div style="font-size:.7rem;color:var(--gray400);margin-top:4px">Separate pool from printing — does not share quota with it.</div>
            </div>

            <div class="fg">
              <div class="flabel"><i class="fa-solid fa-desktop" style="color:var(--g600)"></i> Daily Research/PC-Lab Limit (minutes)</div>
              <input type="number" name="daily_research_minutes" class="fc" min="1" max="1440" value="{{ old('daily_research_minutes', $dailyResearchLimit) }}" required>
              <div style="font-size:.7rem;color:var(--gray400);margin-top:4px">Total minutes across however many 15/30/45/60-min sessions a student books in one day.</div>
            </div>

            <button type="submit" class="btn" style="margin-top:6px">
              <i class="fa-solid fa-floppy-disk"></i> Save Limits
            </button>
          </form>
        </div>
      </div>

      <!-- SYSTEM HOURS -->
      <div class="profile-card">
        <div class="profile-card-hd"><i class="fa-solid fa-clock"></i> System Hours</div>
        <div class="profile-card-body">
          <div class="abox info" style="margin-bottom:16px">
            <i class="fa-solid fa-circle-info"></i>
            <div>Students/faculty can still log in and browse outside these hours, but can't submit new printing, photocopy, or research requests until the IT Center reopens.</div>
          </div>
          <form action="{{ route('admin.settings.hours') }}" method="POST">
            @csrf
            @method('PUT')
            <div class="g2">
              <div class="fg">
                <div class="flabel"><i class="fa-solid fa-door-open" style="color:var(--g600)"></i> Opening Time</div>
                <input type="time" name="system_open_time" class="fc" value="{{ old('system_open_time', $systemOpenTime) }}" required>
              </div>
              <div class="fg">
                <div class="flabel"><i class="fa-solid fa-door-closed" style="color:var(--red)"></i> Closing Time</div>
                <input type="time" name="system_close_time" class="fc" value="{{ old('system_close_time', $systemCloseTime) }}" required>
              </div>
            </div>
            <button type="submit" class="btn" style="margin-top:6px">
              <i class="fa-solid fa-floppy-disk"></i> Save Hours
            </button>
          </form>
        </div>
      </div>

      <!-- VERSION -->
      <div class="profile-card">
        <div class="profile-card-hd"><i class="fa-solid fa-rocket"></i> Version &amp; Update Notice</div>
        <div class="profile-card-body">
          <div class="abox info" style="margin-bottom:16px">
            <i class="fa-solid fa-circle-info"></i>
            <div>When the version number changes, every user (student, faculty, admin) sees a one-time "What's New" popup the next time they land on their dashboard.</div>
          </div>
          <form action="{{ route('admin.settings.version') }}" method="POST">
            @csrf
            @method('PUT')
            <div class="fg">
              <div class="flabel"><i class="fa-solid fa-tag" style="color:var(--g600)"></i> Version Number</div>
              <div style="display:flex;gap:8px">
                <input type="text" name="system_version" id="version-input" class="fc" value="{{ old('system_version', $systemVersion) }}" required style="flex:1">
                <button type="button" class="btn-outline" onclick="autoGenerateVersion()" style="white-space:nowrap">
                  <i class="fa-solid fa-wand-magic-sparkles"></i> Auto-Generate
                </button>
              </div>
              <div style="font-size:.7rem;color:var(--gray400);margin-top:4px">Auto-Generate fills in today's date (e.g. 2026.07.22) — edit freely before saving, or type your own scheme (e.g. 1.4.0).</div>
            </div>
            <div class="fg">
              <div class="flabel"><i class="fa-solid fa-list-check" style="color:var(--g600)"></i> What's New (changelog notes)</div>
              <textarea name="system_version_notes" class="fc" rows="4" placeholder="e.g. - Added messaging between students and IT Center&#10;- Fixed mobile scrolling issue" style="resize:vertical">{{ old('system_version_notes', $systemVersionNotes) }}</textarea>
            </div>
            <button type="submit" class="btn" style="margin-top:6px">
              <i class="fa-solid fa-floppy-disk"></i> Save Version
            </button>
          </form>
        </div>
      </div>

      <!-- MAINTENANCE MODE -->
      <div class="profile-card" style="{{ $maintenanceMode ? 'border-color:var(--red)' : '' }}">
        <div class="profile-card-hd">
          <i class="fa-solid fa-screwdriver-wrench"></i> Maintenance Mode
          @if($maintenanceMode)
          <span class="tag tag-rej" style="margin-left:auto">CURRENTLY DOWN</span>
          @endif
        </div>
        <div class="profile-card-body">
          <div class="abox {{ $maintenanceMode ? 'err' : 'warn' }}" style="margin-bottom:16px">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <div>Turning this ON immediately blocks the public site, login, and all student/faculty pages for everyone except admins — admin access always stays open so you can turn it back off.</div>
          </div>
          <form action="{{ route('admin.settings.maintenance') }}" method="POST">
            @csrf
            @method('PUT')
            <label class="cb-row" style="margin-bottom:14px">
              <input type="checkbox" name="system_maintenance_mode" value="1" {{ $maintenanceMode ? 'checked' : '' }}>
              <strong>Enable maintenance mode (System Down)</strong>
            </label>
            <div class="fg">
              <div class="flabel">Message shown to users</div>
              <textarea name="system_maintenance_message" class="fc" rows="3" style="resize:vertical">{{ old('system_maintenance_message', $maintenanceMessage) }}</textarea>
            </div>
            <button type="submit" class="btn" style="margin-top:6px;{{ $maintenanceMode ? '' : 'background:linear-gradient(135deg,var(--red),#c62828)' }}">
              <i class="fa-solid fa-power-off"></i> {{ $maintenanceMode ? 'Update & Keep Down' : 'Save (Turn System Down)' }}
            </button>
          </form>
        </div>
      </div>

      </div>

    </div>
  </main>
</div>
@push('scripts')
<script>
function autoGenerateVersion(){
  const d = new Date();
  const v = d.getFullYear() + '.' + String(d.getMonth()+1).padStart(2,'0') + '.' + String(d.getDate()).padStart(2,'0');
  document.getElementById('version-input').value = v;
}
</script>
@endpush
@endsection