@php
  $statusClass = [
    'pending'    => 'tag-pend',
    'approved'   => 'tag-appr',
    'processing' => 'tag-res',
    'completed'  => 'tag-done',
    'rejected'   => 'tag-rej',
    'cancelled'  => 'tag-arch',
  ];

  $serviceLabel = match($r->service_type) {
    'printing'  => 'Printing',
    'photocopy' => 'Photocopy',
    'research'  => 'Research / PC-Lab',
    default     => ucfirst($r->service_type),
  };

  $serviceIcon = match($r->service_type) {
    'printing'  => 'fa-print',
    'photocopy' => 'fa-copy',
    'research'  => 'fa-desktop',
    default     => 'fa-file',
  };

  $serviceColor = match($r->service_type) {
    'printing'  => 'var(--blue)',
    'photocopy' => 'var(--orange)',
    'research'  => 'var(--g600)',
    default     => 'var(--gray600)',
  };

  $session = $r->computerSession;
  $detectedPages = (int) ($r->detected_pages ?: 0);
  $totalSheets = $r->service_type === 'printing'
    ? (($detectedPages ?: 1) * (int) ($r->copies ?: 1))
    : null;
@endphp

<div style="padding:18px 20px;background:var(--gray50);border-bottom:1px solid var(--gray200)">
  <div style="display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap">
    <div style="display:flex;align-items:center;gap:12px">
      <div style="width:44px;height:44px;border-radius:11px;background:var(--white);border:1px solid var(--gray200);display:flex;align-items:center;justify-content:center;color:{{ $serviceColor }};font-size:1rem;box-shadow:var(--shadow-sm)">
        <i class="fa-solid {{ $serviceIcon }}"></i>
      </div>
      <div>
        <div style="font-size:.92rem;font-weight:800;color:var(--gray800)">{{ $serviceLabel }} Request</div>
        <div style="font-size:.72rem;color:var(--gray400);margin-top:2px">Submitted {{ $r->created_at->format('M d, Y \a\t g:i A') }}</div>
      </div>
    </div>
    <span class="tag {{ $statusClass[$r->status] ?? '' }}" data-request-status-id="{{ $r->id }}" data-current-status="{{ $r->status }}">{{ strtoupper($r->status) }}</span>
  </div>
</div>

<div style="padding:18px 20px;display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px">
  <div style="padding:11px 12px;border:1px solid var(--gray200);border-radius:9px;background:var(--white)">
    <div style="font-size:.62rem;color:var(--gray400);font-weight:700;text-transform:uppercase;margin-bottom:3px">Request Number</div>
    <div style="font-size:.82rem;font-weight:800;color:var(--gray800);font-family:monospace">{{ $r->request_number }}</div>
  </div>

  <div style="padding:11px 12px;border:1px solid var(--gray200);border-radius:9px;background:var(--white)">
    <div style="font-size:.62rem;color:var(--gray400);font-weight:700;text-transform:uppercase;margin-bottom:3px">Last Updated</div>
    <div style="font-size:.8rem;font-weight:700;color:var(--gray800)" data-request-last-updated-id="{{ $r->id }}">{{ $r->updated_at->format('M d, Y g:i A') }}</div>
  </div>

  @if($r->service_type === 'printing')
    <div style="padding:11px 12px;border:1px solid var(--gray200);border-radius:9px;background:var(--white)">
      <div style="font-size:.62rem;color:var(--gray400);font-weight:700;text-transform:uppercase;margin-bottom:3px">Paper Size</div>
      <div style="font-size:.82rem;font-weight:700;color:var(--gray800)">{{ strtoupper($r->paper_size ?: '—') }}</div>
    </div>
    <div style="padding:11px 12px;border:1px solid var(--gray200);border-radius:9px;background:var(--white)">
      <div style="font-size:.62rem;color:var(--gray400);font-weight:700;text-transform:uppercase;margin-bottom:3px">Copies</div>
      <div style="font-size:.82rem;font-weight:700;color:var(--gray800)">{{ $r->copies ?: '—' }}</div>
    </div>
    <div style="padding:11px 12px;border:1px solid var(--gray200);border-radius:9px;background:var(--white)">
      <div style="font-size:.62rem;color:var(--gray400);font-weight:700;text-transform:uppercase;margin-bottom:3px">Print Type</div>
      <div style="font-size:.82rem;font-weight:700;color:var(--gray800)">{{ ucfirst(str_replace('_', ' ', $r->print_type ?: '—')) }}</div>
    </div>
    <div style="padding:11px 12px;border:1px solid var(--gray200);border-radius:9px;background:var(--white)">
      <div style="font-size:.62rem;color:var(--gray400);font-weight:700;text-transform:uppercase;margin-bottom:3px">Detected Pages</div>
      <div style="font-size:.82rem;font-weight:700;color:var(--gray800)">{{ $detectedPages > 0 ? $detectedPages.' page(s)' : 'Not detected' }}</div>
    </div>
    <div style="padding:11px 12px;border:1px solid var(--gray200);border-radius:9px;background:var(--white)">
      <div style="font-size:.62rem;color:var(--gray400);font-weight:700;text-transform:uppercase;margin-bottom:3px">Total Sheets</div>
      <div style="font-size:.82rem;font-weight:800;color:var(--blue)">{{ $totalSheets }} sheet(s)</div>
    </div>
    <div style="padding:11px 12px;border:1px solid var(--gray200);border-radius:9px;background:var(--white)">
      <div style="font-size:.62rem;color:var(--gray400);font-weight:700;text-transform:uppercase;margin-bottom:3px">Uploaded File</div>
      <div style="font-size:.78rem;font-weight:700;color:var(--gray800);overflow-wrap:anywhere">{{ $r->file_name ?: 'No file available' }}</div>
    </div>
  @elseif($r->service_type === 'photocopy')
    <div style="padding:11px 12px;border:1px solid var(--gray200);border-radius:9px;background:var(--white)">
      <div style="font-size:.62rem;color:var(--gray400);font-weight:700;text-transform:uppercase;margin-bottom:3px">Paper Size</div>
      <div style="font-size:.82rem;font-weight:700;color:var(--gray800)">{{ strtoupper($r->paper_size ?: '—') }}</div>
    </div>
    <div style="padding:11px 12px;border:1px solid var(--gray200);border-radius:9px;background:var(--white)">
      <div style="font-size:.62rem;color:var(--gray400);font-weight:700;text-transform:uppercase;margin-bottom:3px">Copies</div>
      <div style="font-size:.82rem;font-weight:700;color:var(--gray800)">{{ $r->copies ?: '—' }}</div>
    </div>
  @else
    <div style="padding:11px 12px;border:1px solid var(--gray200);border-radius:9px;background:var(--white)">
      <div style="font-size:.62rem;color:var(--gray400);font-weight:700;text-transform:uppercase;margin-bottom:3px">Requested Duration</div>
      <div style="font-size:.82rem;font-weight:700;color:var(--gray800)">{{ $r->duration_minutes ?: 0 }} minutes</div>
    </div>
    <div style="padding:11px 12px;border:1px solid var(--gray200);border-radius:9px;background:var(--white)">
      <div style="font-size:.62rem;color:var(--gray400);font-weight:700;text-transform:uppercase;margin-bottom:3px">PC Assigned</div>
      <div style="font-size:.82rem;font-weight:700;color:var(--g700)">{{ $r->computer->name ?? 'Not yet assigned' }}</div>
    </div>

    @if($session)
      <div style="padding:11px 12px;border:1px solid var(--gray200);border-radius:9px;background:var(--white)">
        <div style="font-size:.62rem;color:var(--gray400);font-weight:700;text-transform:uppercase;margin-bottom:3px">Session Status</div>
        <div style="font-size:.82rem;font-weight:700;color:var(--gray800)" data-session-status-id="{{ $r->id }}">{{ ucfirst($session->status) }}</div>
      </div>
      <div style="padding:11px 12px;border:1px solid var(--gray200);border-radius:9px;background:var(--white)">
        <div style="font-size:.62rem;color:var(--gray400);font-weight:700;text-transform:uppercase;margin-bottom:3px">Session Time</div>
        <div style="font-size:.78rem;font-weight:700;color:var(--gray800)">
          {{ $session->started_at?->format('g:i A') ?? '—' }} –
          {{ $session->ended_at?->format('g:i A') ?? ($session->ends_at?->format('g:i A') ?? '—') }}
        </div>
      </div>
    @endif
  @endif

  <div style="grid-column:1/-1;padding:12px;border:1px solid var(--gray200);border-radius:9px;background:var(--white)">
    <div style="font-size:.62rem;color:var(--gray400);font-weight:700;text-transform:uppercase;margin-bottom:5px">Purpose</div>
    <div style="font-size:.8rem;color:var(--gray700);line-height:1.55;white-space:pre-wrap;overflow-wrap:anywhere">{{ $r->purpose ?: '—' }}</div>
  </div>

  @if($r->reviewed_at || $r->admin)
    <div style="grid-column:1/-1;padding:12px;border:1px solid var(--gray200);border-radius:9px;background:var(--gray50)">
      <div style="font-size:.62rem;color:var(--gray400);font-weight:700;text-transform:uppercase;margin-bottom:5px">Review Information</div>
      <div style="font-size:.78rem;color:var(--gray700);line-height:1.55">
        @if($r->admin)<strong>Reviewed by:</strong> {{ $r->admin->admin_id ?? $r->admin->email ?? 'IT Center Administrator' }}<br>@endif
        @if($r->reviewed_at)<strong>Reviewed on:</strong> {{ $r->reviewed_at->format('M d, Y g:i A') }}@endif
      </div>
    </div>
  @endif

  @if($r->admin_note)
    <div style="grid-column:1/-1;padding:12px;border:1px solid {{ $r->status === 'rejected' ? '#fecaca' : 'var(--gray200)' }};border-radius:9px;background:{{ $r->status === 'rejected' ? 'var(--red-bg)' : 'var(--gray50)' }}">
      <div style="font-size:.62rem;color:{{ $r->status === 'rejected' ? 'var(--red)' : 'var(--gray500)' }};font-weight:700;text-transform:uppercase;margin-bottom:5px">Admin Note</div>
      <div style="font-size:.8rem;color:{{ $r->status === 'rejected' ? 'var(--red)' : 'var(--gray700)' }};line-height:1.55;white-space:pre-wrap;overflow-wrap:anywhere">{{ $r->admin_note }}</div>
    </div>
  @endif
</div>

<div style="padding:14px 20px;border-top:1px solid var(--gray200);display:flex;align-items:center;justify-content:flex-end;gap:8px;flex-wrap:wrap;background:var(--white)">
  @if($r->service_type === 'printing' && $r->file_path)
    <a href="{{ route('requests.file', $r) }}" target="_blank"
      style="background:var(--white);color:var(--blue);border:1.5px solid #90caf9;border-radius:8px;padding:8px 13px;font-size:.74rem;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:6px">
      <i class="fa-solid fa-file-arrow-down"></i> Open Uploaded File
    </a>
  @endif

  @if($r->service_type === 'printing' && $r->status === 'pending')
    <a href="{{ route('requests.printing.edit', $r) }}" data-pending-only-request="{{ $r->id }}"
      style="background:var(--blue-bg);color:var(--blue);border:1.5px solid #90caf9;border-radius:8px;padding:8px 13px;font-size:.74rem;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:6px">
      <i class="fa-solid fa-pen-to-square"></i> Edit Request
    </a>
  @endif

  @if($r->service_type === 'photocopy' && $r->status === 'pending')
    <a href="{{ route('requests.photocopy.edit', $r) }}" data-pending-only-request="{{ $r->id }}"
      style="background:var(--orange-bg);color:var(--orange);border:1.5px solid #ffcc80;border-radius:8px;padding:8px 13px;font-size:.74rem;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:6px">
      <i class="fa-solid fa-pen-to-square"></i> Edit Request
    </a>
  @endif

  <a href="{{ route('requests.report', $r) }}" target="_blank"
    style="background:var(--g600);color:#fff;border:1.5px solid var(--g600);border-radius:8px;padding:8px 13px;font-size:.74rem;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:6px">
    <i class="fa-solid fa-file-pdf"></i> View Receipt
  </a>
</div>
