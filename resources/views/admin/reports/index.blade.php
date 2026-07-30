@extends('layouts.app')
@section('title','Reports | Admin')
@section('body-class','dash-page')
@section('content')
<div class="dash-wrap">
  @include('admin.partials.sidebar')
  <main class="main">
    @include('admin.partials.topbar', ['title'=>'Reports & Analytics','sub'=> session('admin')->role === 'super_admin' ? 'Service usage statistics and insights (all campuses)' : 'Service usage statistics — '.config('campuses.'.session('admin')->campus, session('admin')->campus)])
    <div class="content">

      {{-- DATE + CAMPUS FILTER --}}
      <form class="filter-bar" method="GET" style="margin-bottom:16px;flex-wrap:wrap">
        <div class="fg" style="margin:0;display:flex;align-items:center;gap:8px">
          <span style="font-size:.78rem;font-weight:600;color:var(--gray600)">From</span>
          <input type="date" name="from" class="fc" value="{{ $from }}" style="max-width:160px">
          <span style="font-size:.78rem;font-weight:600;color:var(--gray600)">To</span>
          <input type="date" name="to"   class="fc" value="{{ $to }}"   style="max-width:160px">
        </div>
        @if(session('admin')->role === 'super_admin')
        <div class="fg" style="margin:0;display:flex;align-items:center;gap:8px">
          <span style="font-size:.78rem;font-weight:600;color:var(--gray600)">Campus</span>
          <div class="sw">
            <select name="campus" class="fs" style="max-width:200px">
              <option value="">All Campuses</option>
              @foreach(config('campuses') as $k => $v)
              <option value="{{ $k }}" {{ $campus===$k?'selected':'' }}>{{ $v }}</option>
              @endforeach
            </select>
          </div>
        </div>
        @endif
        <div class="fg" style="margin:0;display:flex;align-items:center;gap:8px">
          <span style="font-size:.78rem;font-weight:600;color:var(--gray600)">Usage Date</span>
          <input type="date" name="usage_date" class="fc" value="{{ $usageDateString }}" style="max-width:160px" max="{{ now()->toDateString() }}">
        </div>
        <button type="submit" class="btn-sm"><i class="fa-solid fa-magnifying-glass"></i> Generate</button>
        <a href="{{ route('admin.reports.pdf', request()->only('from','to','campus')) }}" target="_blank" class="btn-outline"
           style="display:inline-flex;align-items:center;gap:6px;text-decoration:none">
          <i class="fa-solid fa-file-pdf"></i> Download PDF
        </a>
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

      {{-- ── USAGE BY CAMPUS FOR THE SELECTED DAY ── --}}
      <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;flex-wrap:wrap">
        <i class="fa-solid fa-calendar-day" style="color:var(--g600)"></i>
        <span style="font-size:.85rem;font-weight:800;color:var(--gray800)">Usage by Campus</span>
        <span style="font-size:.68rem;color:var(--gray400)">
          — {{ \Carbon\Carbon::parse($usageDateString)->format('M d, Y') }}
          @if($usageDateString === now()->toDateString())
            (today — resets automatically at 12:00 AM)
          @else
            <a href="{{ request()->fullUrlWithQuery(['usage_date' => now()->toDateString()]) }}" style="color:var(--g600);font-weight:700;text-decoration:none">· Jump to today</a>
          @endif
        </span>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">

        {{-- PAPER CONSUMED THAT DAY --}}
        <div class="profile-card">
          <div class="profile-card-hd"><i class="fa-solid fa-layer-group"></i> Paper Consumed</div>
          <div class="profile-card-body" style="padding:0">
            @forelse($todayByCampus as $c)
            <div style="padding:12px 18px;border-bottom:1px solid var(--gray100)">
              <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
                <div style="font-size:.78rem;font-weight:700;color:var(--gray800)">{{ $c->campus_label }}</div>
                <div style="font-size:.85rem;font-weight:800;color:var(--gray800)">{{ $c->printing_sheets + $c->photocopy_sheets }} sheets</div>
              </div>
              <div style="display:flex;gap:14px;font-size:.68rem;color:var(--gray400)">
                <div><i class="fa-solid fa-print" style="color:var(--blue)"></i> Printing: <strong style="color:var(--gray700)">{{ $c->printing_sheets }}</strong></div>
                <div><i class="fa-solid fa-copy" style="color:var(--orange)"></i> Photocopy: <strong style="color:var(--gray700)">{{ $c->photocopy_sheets }}</strong></div>
              </div>
            </div>
            @empty
            <div style="padding:20px;text-align:center;color:var(--gray400);font-size:.78rem">No paper usage on this date.</div>
            @endforelse
          </div>
        </div>

        {{-- PC / RESEARCH USAGE THAT DAY --}}
        <div class="profile-card">
          <div class="profile-card-hd"><i class="fa-solid fa-desktop"></i> PC / Research Usage</div>
          <div class="profile-card-body" style="padding:0">
            @forelse($todayByCampus as $c)
            <div style="padding:12px 18px;border-bottom:1px solid var(--gray100)">
              <div style="display:flex;align-items:center;justify-content:space-between">
                <div style="font-size:.78rem;font-weight:700;color:var(--gray800)">{{ $c->campus_label }}</div>
                <div style="font-size:.85rem;font-weight:800;color:var(--gray800)">
                  @if($c->research_hours > 0){{ $c->research_hours }}h @endif{{ $c->research_mins_rem }}m
                </div>
              </div>
              <div style="font-size:.68rem;color:var(--gray400);margin-top:4px">{{ $c->research_minutes }} minute(s) of PC-Lab time used</div>
            </div>
            @empty
            <div style="padding:20px;text-align:center;color:var(--gray400);font-size:.78rem">No PC/Research usage on this date.</div>
            @endforelse
          </div>
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

        {{-- DAILY PAPER CONSUMPTION --}}
        <div class="profile-card" style="grid-column:1/-1">
          <div class="profile-card-hd"><i class="fa-solid fa-layer-group"></i> Daily Paper Consumption (Sheets)</div>
          <div class="profile-card-body">
            @if($byDayPaperUsage->count())
            <div style="display:flex;gap:16px;margin-bottom:14px;font-size:.72rem">
              <div style="display:flex;align-items:center;gap:6px"><span style="width:10px;height:10px;border-radius:2px;background:var(--blue);display:inline-block"></span> Printing</div>
              <div style="display:flex;align-items:center;gap:6px"><span style="width:10px;height:10px;border-radius:2px;background:var(--orange);display:inline-block"></span> Photocopy</div>
            </div>
            <div style="display:flex;align-items:flex-end;gap:4px;height:120px;padding:0 4px">
              @php $maxSheets = $byDayPaperUsage->max('total_sheets') ?: 1; @endphp
              @foreach($byDayPaperUsage as $day)
              <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:3px" title="{{ $day->date }}: {{ $day->printing_sheets }} printing + {{ $day->photocopy_sheets }} photocopy = {{ $day->total_sheets }} sheets">
                <div style="font-size:.58rem;color:var(--gray400)">{{ $day->total_sheets }}</div>
                <div style="width:100%;border-radius:4px 4px 0 0;overflow:hidden;display:flex;flex-direction:column-reverse;min-height:4px;height:{{ round($day->total_sheets/$maxSheets*100) }}px;transition:height .3s">
                  @if($day->photocopy_sheets > 0)
                  <div style="width:100%;background:var(--orange);height:{{ $day->total_sheets>0 ? round($day->photocopy_sheets/$day->total_sheets*100) : 0 }}%"></div>
                  @endif
                  @if($day->printing_sheets > 0)
                  <div style="width:100%;background:var(--blue);height:{{ $day->total_sheets>0 ? round($day->printing_sheets/$day->total_sheets*100) : 0 }}%"></div>
                  @endif
                </div>
                <div style="font-size:.55rem;color:var(--gray400);white-space:nowrap">{{ \Carbon\Carbon::parse($day->date)->format('M d') }}</div>
              </div>
              @endforeach
            </div>
            <div style="margin-top:14px;padding-top:14px;border-top:1px solid var(--gray200);display:flex;gap:24px">
              <div>
                <div style="font-size:.66rem;color:var(--gray400);text-transform:uppercase;font-weight:700">Total Sheets This Period</div>
                <div style="font-size:1.1rem;font-weight:800;color:var(--gray800)">{{ $byDayPaperUsage->sum('total_sheets') }}</div>
              </div>
              <div>
                <div style="font-size:.66rem;color:var(--gray400);text-transform:uppercase;font-weight:700">Printing</div>
                <div style="font-size:1.1rem;font-weight:800;color:var(--blue)">{{ $byDayPaperUsage->sum('printing_sheets') }}</div>
              </div>
              <div>
                <div style="font-size:.66rem;color:var(--gray400);text-transform:uppercase;font-weight:700">Photocopy</div>
                <div style="font-size:1.1rem;font-weight:800;color:var(--orange)">{{ $byDayPaperUsage->sum('photocopy_sheets') }}</div>
              </div>
            </div>
            @else
            <div style="text-align:center;padding:20px;color:var(--gray400);font-size:.78rem">No paper usage data for this period.</div>
            @endif
          </div>
        </div>

      </div>
    </div>
  </main>
</div>
@endsection