<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>{{ $r->request_number }} - Service Request Report</title>
<style>
  @page { margin: 34px 40px; }
  * { box-sizing: border-box; font-family: 'Helvetica', Arial, sans-serif; }
  body { color: #1e3530; font-size: 11px; }

  .hd-table { width: 100%; border-bottom: 3px solid #1e7d4f; padding-bottom: 12px; margin-bottom: 18px; }
  .hd-table td { vertical-align: middle; }
  .brand-name { font-size: 16px; font-weight: bold; color: #0a3323; }
  .brand-sub { font-size: 9px; color: #4d6b61; margin-top: 2px; }
  .doc-title { font-size: 13px; font-weight: bold; color: #1e7d4f; text-align: right; }
  .doc-sub { font-size: 9px; color: #4d6b61; text-align: right; margin-top: 2px; }

  .status-badge { display: inline-block; padding: 3px 10px; border-radius: 10px; font-size: 9px; font-weight: bold; color: #fff; }

  .section-title { font-size: 10px; font-weight: bold; color: #124530; text-transform: uppercase; letter-spacing: .5px;
    margin: 16px 0 6px; border-bottom: 1px solid #dde6e2; padding-bottom: 4px; }

  table.info { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
  table.info td { padding: 6px 8px; font-size: 10.5px; border-bottom: 1px solid #f0f4f2; }
  table.info td.label { width: 32%; color: #4d6b61; font-weight: bold; }
  table.info td.value { color: #1e3530; }

  .purpose-box { background: #f2fbf7; border-left: 3px solid #2db877; padding: 10px 12px; font-size: 10.5px; line-height: 1.6; margin-top: 6px; }

  .footer { position: fixed; bottom: -20px; left: 0; right: 0; font-size: 8px; color: #8aa89f; text-align: center;
    border-top: 1px solid #dde6e2; padding-top: 6px; }

  .signature-table { width: 100%; margin-top: 40px; }
  .signature-table td { width: 50%; text-align: center; font-size: 9.5px; color: #4d6b61; padding-top: 30px; border-top: 1px solid #8aa89f; }
</style>
</head>
<body>

  <table class="hd-table">
    <tr>
      <td style="width:60%">
        <div class="brand-name">University of Caloocan City</div>
        <div class="brand-sub">IT Center Services System &mdash; {{ config('campuses.'.$r->user->campus, $r->user->campus) }}</div>
      </td>
      <td style="width:40%">
        <div class="doc-title">SERVICE REQUEST REPORT</div>
        <div class="doc-sub">Generated {{ now()->format('M d, Y g:i A') }}</div>
      </td>
    </tr>
  </table>

  <table class="info">
    <tr>
      <td class="label">Request Number</td>
      <td class="value">{{ $r->request_number }}</td>
      <td class="label">Status</td>
      <td class="value">
        @php
          $colors = [
            'pending'=>'#e67e00','approved'=>'#1565c0','processing'=>'#1e7d4f',
            'completed'=>'#1e7d4f','rejected'=>'#e53e3e','cancelled'=>'#4d6b61',
          ];
        @endphp
        <span class="status-badge" style="background:{{ $colors[$r->status] ?? '#4d6b61' }}">{{ strtoupper($r->status) }}</span>
      </td>
    </tr>
    <tr>
      <td class="label">Service Type</td>
      <td class="value">{{ ucfirst($r->service_type) }}</td>
      <td class="label">Date Submitted</td>
      <td class="value">{{ $r->created_at->format('M d, Y g:i A') }}</td>
    </tr>
  </table>

  <div class="section-title">Requested By</div>
  <table class="info">
    <tr>
      <td class="label">Name</td>
      <td class="value">{{ $r->user->full_name }}</td>
      <td class="label">ID Number</td>
      <td class="value">{{ $r->user->id_number }}</td>
    </tr>
    <tr>
      <td class="label">Type</td>
      <td class="value">{{ ucfirst(str_replace('_',' ',$r->user->user_type)) }}</td>
      <td class="label">Campus</td>
      <td class="value">{{ config('campuses.'.$r->user->campus, $r->user->campus) }}</td>
    </tr>
  </table>

  <div class="section-title">Request Details</div>
  <table class="info">
    @if($r->service_type === 'printing')
      <tr><td class="label">Paper Size</td><td class="value">{{ strtoupper($r->paper_size) }}</td>
          <td class="label">Copies</td><td class="value">{{ $r->copies }}</td></tr>
      <tr><td class="label">Print Type</td><td class="value">{{ ucfirst(str_replace('_',' ',$r->print_type ?? '-')) }}</td>
          <td class="label">Detected Pages</td><td class="value">{{ $r->detected_pages ?? '-' }}</td></tr>
      <tr><td class="label">File Name</td><td class="value" colspan="3">{{ $r->file_name ?? '-' }}</td></tr>
    @elseif($r->service_type === 'photocopy')
      <tr><td class="label">Paper Size</td><td class="value">{{ strtoupper($r->paper_size) }}</td>
          <td class="label">Copies</td><td class="value">{{ $r->copies }}</td></tr>
    @else
      <tr><td class="label">Duration</td><td class="value">{{ $r->duration_minutes }} minutes</td>
          <td class="label">PC Assigned</td><td class="value">{{ $r->computer->name ?? 'Not yet assigned' }}</td></tr>
      @if($r->computerSession)
      <tr><td class="label">Session Start</td><td class="value">{{ $r->computerSession->started_at?->format('g:i A') ?? '-' }}</td>
          <td class="label">Session End</td><td class="value">{{ $r->computerSession->ended_at?->format('g:i A') ?? ($r->computerSession->ends_at?->format('g:i A') ?? '-') }}</td></tr>
      @endif
    @endif
  </table>

  <div class="section-title">Purpose</div>
  <div class="purpose-box">{{ $r->purpose }}</div>

  @if($r->status === 'rejected' && $r->admin_note)
  <div class="section-title" style="color:#e53e3e">Rejection Note</div>
  <div class="purpose-box" style="background:#fff0f0;border-left-color:#e53e3e;color:#7a1212">{{ $r->admin_note }}</div>
  @endif

  <div class="section-title">Review</div>
  <table class="info">
    <tr>
      <td class="label">Reviewed By</td>
      <td class="value">{{ $r->admin->admin_id ?? 'Pending review' }}</td>
      <td class="label">Reviewed At</td>
      <td class="value">{{ $r->reviewed_at?->format('M d, Y g:i A') ?? '-' }}</td>
    </tr>
  </table>

  <table class="signature-table">
    <tr>
      <td>Requesting {{ ucfirst(str_replace('_',' ',$r->user->user_type)) }}</td>
      <td>IT Center Administrator</td>
    </tr>
  </table>

  <div class="footer">
    This is a system-generated report from the UCC IT Center Services System &middot; {{ $r->request_number }} &middot; Page 1
  </div>

</body>
</html>