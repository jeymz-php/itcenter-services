<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>IT Center Report {{ $from }} to {{ $to }}</title>
<style>
  @page { margin: 100px 36px 70px 36px; }
  * { box-sizing: border-box; font-family: 'Helvetica', Arial, sans-serif; }
  body { color: #1e3530; font-size: 10px; }

  header { position: fixed; top: -80px; left: 0; right: 0; height: 70px; }
  footer { position: fixed; bottom: -50px; left: 0; right: 0; height: 40px; }

  .hd-table, .ft-table { width: 100%; }
  .hd-table td, .ft-table td { vertical-align: middle; }
  .hd-logo { width: 44px; height: 44px; object-fit: contain; }
  .hd-title { font-size: 15px; font-weight: bold; color: #0a3323; }
  .hd-sub { font-size: 9px; color: #4d6b61; margin-top: 2px; }
  .hd-meta { text-align: right; font-size: 8.5px; color: #4d6b61; line-height: 1.6; }
  .hd-rule { border-top: 2px solid #1e7d4f; margin-top: 6px; }

  .ft-logo { width: 60px; height: 34px; object-fit: contain; }
  .ft-text { font-size: 7.5px; color: #8aa89f; }
  .ft-rule { border-top: 1px solid #dde6e2; margin-bottom: 6px; }
  .pagenum:before { content: counter(page); }

  .section-title { font-size: 11px; font-weight: bold; color: #124530; text-transform: uppercase; letter-spacing: .4px;
    margin: 16px 0 8px; border-bottom: 1.5px solid #dde6e2; padding-bottom: 5px; }

  .stat-row { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
  .stat-row td { width: 20%; text-align: center; padding: 10px 4px; background: #f2fbf7; border: 1px solid #dde6e2; }
  .stat-val { font-size: 15px; font-weight: bold; color: #124530; }
  .stat-lbl { font-size: 7.5px; color: #4d6b61; text-transform: uppercase; margin-top: 2px; }

  table.data { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
  table.data th { background: #124530; color: #fff; font-size: 8px; text-transform: uppercase; padding: 6px 8px; text-align: left; }
  table.data td { padding: 5px 8px; font-size: 8.7px; border-bottom: 1px solid #f0f4f2; }
  table.data tr:nth-child(even) td { background: #f9fcfb; }
  .num { text-align: right; font-weight: bold; }
  .center { text-align: center; }
</style>
</head>
<body>

<header>
  <table class="hd-table">
    <tr>
      <td style="width:50px">
        @if(file_exists(public_path('images/UCC_Logo.png')))
          <img class="hd-logo" src="{{ public_path('images/UCC_Logo.png') }}">
        @endif
      </td>
      <td>
        <div class="hd-title">University of Caloocan City — IT Center Services</div>
        <div class="hd-sub">Service Usage &amp; Analytics Report</div>
      </td>
      <td class="hd-meta">
        Period: {{ \Carbon\Carbon::parse($from)->format('M d, Y') }} – {{ \Carbon\Carbon::parse($to)->format('M d, Y') }}<br>
        Scope: {{ $campusLabel }}<br>
        Generated: {{ now()->format('M d, Y g:i A') }} by {{ $generatedBy }}
      </td>
    </tr>
  </table>
  <div class="hd-rule"></div>
</header>

<footer>
  <div class="ft-rule"></div>
  <table class="ft-table">
    <tr>
      <td style="width:70px">
        @if(file_exists(public_path('images/caloocannewlogo.png')))
          <img class="ft-logo" src="{{ public_path('images/caloocannewlogo.png') }}">
        @endif
      </td>
      <td class="ft-text">
        UCC IT Center Services System — System-generated report. Not valid without official letterhead when printed for external use.
      </td>
      <td class="ft-text" style="text-align:right;width:60px">
        Page <span class="pagenum"></span>
      </td>
    </tr>
  </table>
</footer>

<div class="section-title">Summary</div>
<table class="stat-row">
  <tr>
    <td><div class="stat-val">{{ $totals['requests'] }}</div><div class="stat-lbl">Total Requests</div></td>
    <td><div class="stat-val">{{ $totals['completed'] }}</div><div class="stat-lbl">Completed</div></td>
    <td><div class="stat-val">{{ $totals['pending'] }}</div><div class="stat-lbl">Still Pending</div></td>
    <td><div class="stat-val">{{ $totals['users'] }}</div><div class="stat-lbl">New Users</div></td>
    <td><div class="stat-val">{{ $totals['pc_hours'] }}h</div><div class="stat-lbl">PC Hours Used</div></td>
  </tr>
</table>

<div class="section-title">Requests by Service Type</div>
<table class="data">
  <thead><tr><th>Service</th><th class="num">Total</th><th class="num">Completed</th><th class="num">Rejected</th></tr></thead>
  <tbody>
    @forelse($byService as $row)
    <tr>
      <td style="text-transform:capitalize">{{ $row->service_type }}</td>
      <td class="num">{{ $row->total }}</td>
      <td class="num">{{ $row->completed }}</td>
      <td class="num">{{ $row->rejected }}</td>
    </tr>
    @empty
    <tr><td colspan="4" class="center">No data for this period.</td></tr>
    @endforelse
  </tbody>
</table>

<div class="section-title">Requests by Campus</div>
<table class="data">
  <thead><tr><th>Campus</th><th class="num">Total Requests</th></tr></thead>
  <tbody>
    @forelse($byCampus as $row)
    <tr>
      <td>{{ config('campuses.'.$row->campus, $row->campus) }}</td>
      <td class="num">{{ $row->total }}</td>
    </tr>
    @empty
    <tr><td colspan="2" class="center">No data for this period.</td></tr>
    @endforelse
  </tbody>
</table>

<div class="section-title">Per-User Service Usage</div>
<table class="data">
  <thead>
    <tr>
      <th>Name</th><th>ID Number</th><th>Type</th><th>Campus</th>
      <th class="num">Printing</th><th class="num">Photocopy</th><th class="num">Research</th>
      <th class="num">Completed</th><th class="num">Total</th>
    </tr>
  </thead>
  <tbody>
    @forelse($userBreakdown as $u)
    <tr>
      <td>{{ $u->full_name }}</td>
      <td>{{ $u->id_number }}</td>
      <td>{{ ucfirst(str_replace('_',' ',$u->user_type)) }}</td>
      <td>{{ config('campuses.'.$u->campus, $u->campus) }}</td>
      <td class="num">{{ $u->printing_count }}</td>
      <td class="num">{{ $u->photocopy_count }}</td>
      <td class="num">{{ $u->research_count }}</td>
      <td class="num">{{ $u->completed_count }}</td>
      <td class="num">{{ $u->total_count }}</td>
    </tr>
    @empty
    <tr><td colspan="9" class="center">No users with requests in this period.</td></tr>
    @endforelse
  </tbody>
</table>

</body>
</html>