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

      <div class="profile-card" style="max-width:560px">
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

    </div>
  </main>
</div>
@endsection