<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>{{ $r->request_number }} - Receipt</title>
<style>
  @page { margin: 14px 10px; }
  * { box-sizing: border-box; }
  body { font-family: 'Courier New', Courier, monospace; color: #1a1a1a; font-size: 8.3px; line-height: 1.5; }

  .center { text-align: center; }
  .bold   { font-weight: bold; }

  .logos-row { width: 100%; margin-bottom: 4px; }
  .logos-row td { text-align: center; vertical-align: middle; }
  .logo-ucc { max-width: 40px; max-height: 40px; width: auto; height: auto; }
  .logo-caloocan { width: 60px; height: 34px; }

  .brand-name { font-size: 10.5px; font-weight: bold; letter-spacing: .3px; margin-top: 2px; }
  .brand-sub  { font-size: 7.6px; color: #444; margin-top: 1px; }

  .dashed { border-top: 1px dashed #999; margin: 7px 0; width: 100%; }

  .doc-title { font-size: 9.3px; font-weight: bold; text-align: center; letter-spacing: .5px; margin-bottom: 2px; }
  .doc-sub   { font-size: 7.3px; text-align: center; color: #555; }

  table.rowset { width: 100%; border-collapse: collapse; }
  table.rowset td { padding: 1.5px 0; font-size: 8px; vertical-align: top; }
  table.rowset td.lbl { color: #444; width: 46%; }
  table.rowset td.val { text-align: right; font-weight: bold; width: 54%; word-wrap: break-word; }

  .section-lbl { font-size: 7.4px; font-weight: bold; letter-spacing: .6px; color: #333; margin-bottom: 3px; }

  .status-line { text-align: center; font-size: 9px; font-weight: bold; padding: 3px 0; letter-spacing: 1px; }

  .purpose-box { font-size: 7.8px; margin-top: 3px; line-height: 1.6; }

  .foot-note { text-align: center; font-size: 7.2px; color: #555; margin-top: 4px; line-height: 1.6; }
  .barcode-txt { text-align: center; font-size: 10px; letter-spacing: 3px; margin: 6px 0 2px; font-weight: bold; }
</style>
</head>
<body>

  <table class="logos-row">
    <tr>
      <td style="width:33%">
        @if(file_exists(public_path('images/UCC_Logo.png')))
          <img class="logo-ucc" src="{{ public_path('images/UCC_Logo.png') }}">
        @endif
      </td>
      <td style="width:33%">
        @if(file_exists(public_path('images/caloocannewlogo.png')))
          <img class="logo-caloocan" src="{{ public_path('images/caloocannewlogo.png') }}">
        @endif
      </td>
    </tr>
  </table>

  <div class="center brand-name">UNIVERSITY OF CALOOCAN CITY</div>
  <div class="center brand-sub">IT Center Services System</div>
  <div class="center brand-sub">{{ config('campuses.'.$r->user->campus, $r->user->campus) }}</div>

  <div class="dashed"></div>

  <div class="doc-title">SERVICE REQUEST RECEIPT</div>
  <div class="doc-sub">{{ $r->created_at->format('M d, Y  g:i A') }}</div>

  <div class="dashed"></div>

  <table class="rowset">
    <tr><td class="lbl">Request No.</td><td class="val">{{ $r->request_number }}</td></tr>
    <tr><td class="lbl">Service</td><td class="val">{{ strtoupper($r->service_type) }}</td></tr>
  </table>

  @php
    $statusLabels = [
      'pending'=>'PENDING REVIEW','approved'=>'APPROVED','processing'=>'PROCESSING',
      'completed'=>'COMPLETED','rejected'=>'REJECTED','cancelled'=>'CANCELLED',
    ];
  @endphp
  <div class="status-line">*** {{ $statusLabels[$r->status] ?? strtoupper($r->status) }} ***</div>

  <div class="dashed"></div>

  <div class="section-lbl">CUSTOMER</div>
  <table class="rowset">
    <tr><td class="lbl">Name</td><td class="val">{{ $r->user->full_name }}</td></tr>
    <tr><td class="lbl">ID No.</td><td class="val">{{ $r->user->id_number }}</td></tr>
    <tr><td class="lbl">Type</td><td class="val">{{ ucfirst(str_replace('_',' ',$r->user->user_type)) }}</td></tr>
    <tr><td class="lbl">Campus</td><td class="val">{{ config('campuses.'.$r->user->campus, $r->user->campus) }}</td></tr>
  </table>

  <div class="dashed"></div>

  <div class="section-lbl">SERVICE DETAILS</div>
  <table class="rowset">
    @if($r->service_type === 'printing')
      <tr><td class="lbl">Paper Size</td><td class="val">{{ strtoupper($r->paper_size) }}</td></tr>
      <tr><td class="lbl">Copies</td><td class="val">{{ $r->copies }}</td></tr>
      <tr><td class="lbl">Print Type</td><td class="val">{{ ucfirst(str_replace('_',' ',$r->print_type ?? '-')) }}</td></tr>
      <tr><td class="lbl">Pages</td><td class="val">{{ $r->detected_pages ?? '-' }}</td></tr>
      @if($r->detected_pages && $r->copies)
      <tr><td class="lbl">Total Sheets</td><td class="val">{{ (int)$r->detected_pages * (int)$r->copies }}</td></tr>
      @endif
    @elseif($r->service_type === 'photocopy')
      <tr><td class="lbl">Paper Size</td><td class="val">{{ strtoupper($r->paper_size) }}</td></tr>
      <tr><td class="lbl">Copies</td><td class="val">{{ $r->copies }}</td></tr>
    @else
      <tr><td class="lbl">Duration</td><td class="val">{{ $r->duration_minutes }} min</td></tr>
      <tr><td class="lbl">PC Unit</td><td class="val">{{ $r->computer->name ?? 'Not yet assigned' }}</td></tr>
      @if($r->computerSession)
      <tr><td class="lbl">Session Start</td><td class="val">{{ $r->computerSession->started_at?->format('g:i A') ?? '-' }}</td></tr>
      <tr><td class="lbl">Session End</td><td class="val">{{ $r->computerSession->ended_at?->format('g:i A') ?? ($r->computerSession->ends_at?->format('g:i A') ?? '-') }}</td></tr>
      @endif
    @endif
  </table>

  <div class="dashed"></div>

  <div class="section-lbl">PURPOSE</div>
  <div class="purpose-box">{{ $r->purpose }}</div>

  @if($r->status === 'rejected' && $r->admin_note)
  <div class="dashed"></div>
  <div class="section-lbl">REJECTION NOTE</div>
  <div class="purpose-box">{{ $r->admin_note }}</div>
  @endif

  <div class="dashed"></div>

  <table class="rowset">
    <tr><td class="lbl">Reviewed By</td><td class="val">{{ $r->admin->admin_id ?? 'Pending' }}</td></tr>
    <tr><td class="lbl">Reviewed At</td><td class="val">{{ $r->reviewed_at?->format('M d, g:i A') ?? '-' }}</td></tr>
  </table>

  <div class="dashed"></div>

  <div class="barcode-txt">*{{ $r->request_number }}*</div>

  <div class="foot-note">
    This is a system-generated receipt.
    Keep for your records.<br>
    UCC IT Center Services System
    Printed {{ now()->format('M d, Y g:i A') }}
  </div>

</body>
</html>