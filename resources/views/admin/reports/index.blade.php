@extends('layouts.app')
@section('title','Reports | Admin')
@section('body-class','dash-page')
@section('content')
<div class="dash-wrap">
  @include('admin.partials.sidebar')
  <main class="main">
    @include('admin.partials.topbar', ['title'=>'Reports & Analytics','sub'=>'Service usage statistics and insights'])
    <div class="content">

      {{-- DATE FILTER --}}
      <form class="filter-bar" method="GET" style="margin-bottom:16px">
        <div class="fg" style="margin:0;display:flex;align-items:center;gap:8px">
          <span style="font-size:.78rem;font-weight:600;color:var(--gray600)">From</span>
          <input type="date" name="from" class="fc" value="{{ $from }}" style="max-width:160px">
          <span style="font-size:.78rem;font-weight:600;color:var(--gray600)">To</span>
          <input type="date" name="to"   class="fc" value="{{ $to }}"   style="max-width:160px">
        </div>
        <button type="submit" class="btn-sm"><i class="fa-solid fa-magnifying-glass"></i> Generate</button>
      </form>

      {{-- SUMMARY CARDS --}}
      <div class="stat-grid" style="margin-bottom:16px">
        <div class="stat-card">
          <div class="stat-ico" style="background:var(--g100);color:var(--g700)"><i class="fa-solid fa-file-lines"></i></div>
          <div><div class="stat-lbl">Total Requests</div><div class="stat-val">{{ $totals['requests'] }}</div></div>
        </div>
        <div class="stat-card">
          <div class="stat-ico" style="background:var(--g100);color:var(--g600)"><i class="fa-solid fa-check-double"></i></div>
          <div><div class="stat-lbl">Completed</div><div class="stat-val">{{ $totals['completed'] }}</div></div>
        </div>
        <div class="stat-card">
          <div class="stat-ico" style="background:var(--orange-bg);color:var(--orange)"><i class="fa-solid fa-hourglass-half"></i></div>
          <div><div class="stat-lbl">Still Pending</div><div class="stat-val">{{ $totals['pending'] }}</div></div>
        </div>
        <div class="stat-card">
          <div class="stat-ico" style="background:var(--blue-bg);color:var(--blue)"><i class="fa-solid fa-desktop"></i></div>
          <div><div class="stat-lbl">PC Hours Used</div><div class="stat-val">{{ $totals['pc_hours'] }}h</div></div>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">

        {{-- BY SERVICE --}}
        <div class="profile-card">
          <div class="profile-card-hd"><i class="fa-solid fa-chart-pie"></i> Requests by Service Type</div>
          <div class="profile-card-body" style="padding:0">
            @forelse($byService as $row)
            <div style="padding:12px 18px;border-bottom:1px solid var(--gray100);display:flex;align-items:center;gap:12px">
              <div style="width:36px;height:36px;border-radius:9px;flex-shrink:0;
                background:{{ $row->service_type==='printing'?'var(--blue-bg)':($row->service_type==='photocopy'?'var(--orange-bg)':'var(--g100)') }};
                display:flex;align-items:center;justify-content:center;
                color:{{ $row->service_type==='printing'?'var(--blue)':($row->service_type==='photocopy'?'var(--orange)':'var(--g600)') }};
                font-size:.9rem">
                <i class="fa-solid {{ $row->service_type==='printing'?'fa-print':($row->service_type==='photocopy'?'fa-copy':'fa-desktop') }}"></i>
              </div>
              <div style="flex:1">
                <div style="font-size:.8rem;font-weight:700;color:var(--gray800);text-transform:capitalize">{{ $row->service_type }}</div>
                <div style="background:var(--gray200);border-radius:4px;height:5px;margin-top:5px;overflow:hidden">
                  <div style="height:100%;border-radius:4px;background:var(--g500);width:{{ $totals['requests']>0?round($row->total/$totals['requests']*100):0 }}%"></div>
                </div>
              </div>
              <div style="text-align:right">
                <div style="font-size:.95rem;font-weight:800;color:var(--gray800)">{{ $row->total }}</div>
                <div style="font-size:.65rem;color:var(--gray400)">{{ $row->completed }} done</div>
              </div>
            </div>
            @empty
            <div style="padding:20px;text-align:center;color:var(--gray400);font-size:.78rem">No data for this period.</div>
            @endforelse
          </div>
        </div>

        {{-- BY CAMPUS --}}
        <div class="profile-card">
          <div class="profile-card-hd"><i class="fa-solid fa-building-columns"></i> Requests by Campus</div>
          <div class="profile-card-body" style="padding:0">
            @forelse($byCampus as $row)
            <div style="padding:12px 18px;border-bottom:1px solid var(--gray100);display:flex;align-items:center;gap:12px">
              <div style="width:36px;height:36px;border-radius:9px;flex-shrink:0;background:var(--g100);display:flex;align-items:center;justify-content:center;color:var(--g600);font-size:.9rem">
                <i class="fa-solid fa-building-columns"></i>
              </div>
              <div style="flex:1">
                <div style="font-size:.78rem;font-weight:700;color:var(--gray800)">{{ config('campuses.'.$row->campus,'Unknown') }}</div>
                <div style="background:var(--gray200);border-radius:4px;height:5px;margin-top:5px;overflow:hidden">
                  <div style="height:100%;border-radius:4px;background:var(--g400);width:{{ $totals['requests']>0?round($row->total/$totals['requests']*100):0 }}%"></div>
                </div>
              </div>
              <div style="font-size:.95rem;font-weight:800;color:var(--gray800)">{{ $row->total }}</div>
            </div>
            @empty
            <div style="padding:20px;text-align:center;color:var(--gray400);font-size:.78rem">No data for this period.</div>
            @endforelse
          </div>
        </div>

        {{-- DAILY TREND --}}
        <div class="profile-card" style="grid-column:1/-1">
          <div class="profile-card-hd"><i class="fa-solid fa-chart-line"></i> Daily Request Trend</div>
          <div class="profile-card-body">
            @if($byDay->count())
            <div style="display:flex;align-items:flex-end;gap:4px;height:120px;padding:0 4px">
              @php $maxDay = $byDay->max('total') ?: 1; @endphp
              @foreach($byDay as $day)
              <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:3px" title="{{ $day->date }}: {{ $day->total }} requests">
                <div style="font-size:.58rem;color:var(--gray400)">{{ $day->total }}</div>
                <div style="width:100%;border-radius:4px 4px 0 0;background:var(--g500);min-height:4px;height:{{ round($day->total/$maxDay*100) }}px;transition:height .3s"></div>
                <div style="font-size:.55rem;color:var(--gray400);white-space:nowrap">{{ \Carbon\Carbon::parse($day->date)->format('M d') }}</div>
              </div>
              @endforeach
            </div>
            @else
            <div style="text-align:center;padding:20px;color:var(--gray400);font-size:.78rem">No daily data for this period.</div>
            @endif
          </div>
        </div>

      </div>
    </div>
  </main>
</div>
@endsection