@extends('layouts.app')
@section('title','Request Details | Admin')
@section('body-class','dash-page')
@section('content')

@php
  $computers = $sr->service_type === 'research'
    ? \App\Models\Computer::where('status','available')->where('campus', $sr->user->campus)->orderBy('sort_order')->get()
    : collect();
  $session = $sr->computerSession;
@endphp

<!-- REJECT MODAL -->
<div class="modal-bg" id="rejectModal">
  <div class="modal-box">
    <div class="modal-hd">
      <h3><i class="fa-solid fa-xmark" style="color:var(--red);margin-right:6px"></i>Reject Request</h3>
      <button class="modal-close" onclick="closeModal('rejectModal')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form action="{{ route('admin.service-requests.reject', $sr) }}" method="POST">
      @csrf
      <div class="modal-body">
        <div class="fg"><div class="flabel">Reason</div>
          <textarea name="admin_note" class="fc" rows="3" placeholder="State reason for rejection..." required style="resize:vertical"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="modal-btn secondary" onclick="closeModal('rejectModal')">Cancel</button>
        <button type="submit" class="modal-btn danger"><i class="fa-solid fa-xmark"></i> Reject</button>
      </div>
    </form>
  </div>
</div>

<!-- ASSIGN PC MODAL -->
@if($sr->service_type === 'research' && $sr->status === 'approved')
<div class="modal-bg" id="assignPcModal">
  <div class="modal-box">
    <div class="modal-hd">
      <h3><i class="fa-solid fa-desktop" style="color:var(--g600);margin-right:6px"></i>Assign PC to Session</h3>
      <button class="modal-close" onclick="closeModal('assignPcModal')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form action="{{ route('admin.service-requests.assign-pc', $sr) }}" method="POST">
      @csrf
      <div class="modal-body">
        <div class="abox info" style="margin-bottom:14px">
          <i class="fa-solid fa-info-circle"></i>
          <div>Assigning a PC will <strong>immediately start</strong> the {{ $sr->duration_minutes }}-minute session timer.</div>
        </div>
        <div class="fg">
          <div class="flabel">Select Available PC</div>
          @if($computers->count())
          <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px">
            @foreach($computers as $pc)
            <label style="cursor:pointer">
              <input type="radio" name="computer_id" value="{{ $pc->id }}" style="display:none" required>
              <div class="pc-opt">
                <i class="fa-solid fa-computer" style="font-size:1.2rem;color:var(--g600);margin-bottom:5px"></i>
                <div style="font-size:.78rem;font-weight:800">{{ $pc->name }}</div>
                <div style="font-size:.65rem;color:var(--gray400);margin-top:2px">{{ Str::limit($pc->specs,30) }}</div>
                <span class="tag tag-active" style="margin-top:5px;font-size:.6rem">AVAILABLE</span>
              </div>
            </label>
            @endforeach
          </div>
          @else
          <div class="abox warn"><i class="fa-solid fa-triangle-exclamation"></i> No PCs available right now.</div>
          @endif
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="modal-btn secondary" onclick="closeModal('assignPcModal')">Cancel</button>
        @if($computers->count())
        <button type="submit" class="modal-btn primary"><i class="fa-solid fa-play"></i> Assign & Start Session</button>
        @endif
      </div>
    </form>
  </div>
</div>
@endif

<!-- EXTEND SESSION MODAL -->
@if($session && in_array($session->status, ['active','extended']))
@php
  $extendDailyLimit = \App\Models\ServiceRequest::dailyResearchLimit();
  $extendUsedToday = \App\Models\ServiceRequest::minutesUsedToday($sr->user_id);
  $extendRemaining = max(0, $extendDailyLimit - $extendUsedToday);
@endphp
<div class="modal-bg" id="extendModal">
  <div class="modal-box">
    <div class="modal-hd">
      <h3><i class="fa-solid fa-clock" style="color:var(--g600);margin-right:6px"></i>Extend Session</h3>
      <button class="modal-close" onclick="closeModal('extendModal')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form action="{{ route('admin.service-requests.extend-session', $sr) }}" method="POST">
      @csrf
      <div class="modal-body">
        <div style="background:{{ $extendRemaining > 0 ? 'var(--g50)' : '#fff1f2' }};border:1px solid {{ $extendRemaining > 0 ? 'var(--g200)' : '#fecdd3' }};border-radius:10px;padding:11px 13px;margin-bottom:14px;font-size:.76rem;line-height:1.55;color:var(--gray700)">
          <div style="font-weight:800;color:{{ $extendRemaining > 0 ? 'var(--g700)' : 'var(--red)' }};margin-bottom:2px">
            <i class="fa-solid fa-gauge-high" style="margin-right:5px"></i>
            Daily usage: {{ $extendUsedToday }}/{{ $extendDailyLimit }} minutes
          </div>
          Session extensions count toward the daily Research / PC Lab limit.
          @if($extendRemaining > 0)
            The user has <strong>{{ $extendRemaining }} minute(s)</strong> remaining today.
          @else
            The daily limit has been reached. No further extension can be added until 12:00 AM.
          @endif
        </div>
        <div class="fg">
          <div class="flabel">Extend By</div>
          <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px">
            @foreach([15,30,45,60] as $min)
            @php $canUseExtension = $min <= $extendRemaining; @endphp
            <label style="cursor:{{ $canUseExtension ? 'pointer' : 'not-allowed' }};opacity:{{ $canUseExtension ? '1' : '.42' }}" title="{{ $canUseExtension ? $min.' minutes' : 'Exceeds the remaining daily limit' }}">
              <input type="radio" name="extend_minutes" value="{{ $min }}" style="display:none" {{ $canUseExtension ? 'required' : 'disabled' }}>
              <div class="dur-opt" style="padding:12px 8px">
                <div style="font-size:1.2rem;font-weight:800;color:var(--g700)">{{ $min }}</div>
                <div style="font-size:.68rem;color:var(--gray400)">minutes</div>
              </div>
            </label>
            @endforeach
          </div>
          @error('extend_minutes')
            <div style="margin-top:9px;color:var(--red);font-size:.74rem;font-weight:700"><i class="fa-solid fa-circle-exclamation" style="margin-right:4px"></i>{{ $message }}</div>
          @enderror
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="modal-btn secondary" onclick="closeModal('extendModal')">Cancel</button>
        <button type="submit" class="modal-btn primary" {{ $extendRemaining < 15 ? 'disabled' : '' }} style="{{ $extendRemaining < 15 ? 'opacity:.5;cursor:not-allowed' : '' }}"><i class="fa-solid fa-clock"></i> Extend</button>
      </div>
    </form>
  </div>
</div>
@endif

<div class="dash-wrap">
  @include('admin.partials.sidebar')
  <main class="main">
    @include('admin.partials.topbar', ['title' => 'Request Details', 'sub' => $sr->request_number])
    <div class="content">

      @if(session('success'))
        <div class="abox ok" style="margin-bottom:14px"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
      @endif
      @if($errors->any())
        <div class="abox err" style="margin-bottom:14px"><i class="fa-solid fa-triangle-exclamation"></i> {{ $errors->first() }}</div>
      @endif

      <div style="display:flex;justify-content:flex-end;margin-bottom:14px">
        <a href="{{ route('admin.service-requests.report', $sr) }}" target="_blank"
          style="background:var(--white);color:var(--g700);border:1.5px solid var(--g300);border-radius:8px;padding:8px 16px;font-size:.78rem;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:6px;white-space:nowrap;text-decoration:none">
          <i class="fa-solid fa-file-pdf"></i> View Receipt
        </a>
      </div>

      {{-- REQUESTER'S DAILY USAGE — full width, sits above the 2-column grid so it
           never creates a mismatched-height column with empty space beneath it --}}
      @php
        $reqPrintLimit = \App\Models\ServiceRequest::dailyPrintingLimit();
        $reqPrintUsed  = \App\Models\ServiceRequest::printingPagesUsedToday($sr->user_id);
        $reqPrintLeft  = \App\Models\ServiceRequest::printingPagesRemainingToday($sr->user_id);
        $reqCopyLimit  = \App\Models\ServiceRequest::dailyPhotocopyLimit();
        $reqCopyUsed   = \App\Models\ServiceRequest::photocopyPagesUsedToday($sr->user_id);
        $reqCopyLeft   = \App\Models\ServiceRequest::photocopyPagesRemainingToday($sr->user_id);
        $reqMinLimit   = \App\Models\ServiceRequest::dailyResearchLimit();
        $reqMinUsed    = \App\Models\ServiceRequest::minutesUsedToday($sr->user_id);
        $reqMinLeft    = \App\Models\ServiceRequest::minutesRemainingToday($sr->user_id);
      @endphp
      <div class="profile-card" style="margin-bottom:16px">
        <div class="profile-card-hd"><i class="fa-solid fa-gauge-high"></i> {{ $sr->user->first_name }}'s Daily Usage</div>
        <div class="profile-card-body" style="display:grid;grid-template-columns:repeat(3,1fr);gap:18px">
          <div>
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px">
              <span style="font-size:.74rem;color:var(--gray600)"><i class="fa-solid fa-print" style="color:var(--blue);margin-right:4px"></i>Printing</span>
              <span style="font-size:.74rem;font-weight:800;color:{{ $reqPrintLeft<=0?'var(--red)':'var(--blue)' }}">{{ $reqPrintUsed }}/{{ $reqPrintLimit }}</span>
            </div>
            <div style="background:var(--gray100);border-radius:8px;height:6px;overflow:hidden">
              @php $reqPrintPct = $reqPrintLimit>0 ? min(100, round($reqPrintUsed/$reqPrintLimit*100)) : 0; @endphp
              <div style="height:100%;border-radius:8px;width:{{ $reqPrintPct }}%;background:{{ $reqPrintLeft<=0?'var(--red)':'var(--blue)' }}"></div>
            </div>
          </div>
          <div>
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px">
              <span style="font-size:.74rem;color:var(--gray600)"><i class="fa-solid fa-copy" style="color:var(--orange);margin-right:4px"></i>Photocopy</span>
              <span style="font-size:.74rem;font-weight:800;color:{{ $reqCopyLeft<=0?'var(--red)':'var(--orange)' }}">{{ $reqCopyUsed }}/{{ $reqCopyLimit }}</span>
            </div>
            <div style="background:var(--gray100);border-radius:8px;height:6px;overflow:hidden">
              @php $reqCopyPct = $reqCopyLimit>0 ? min(100, round($reqCopyUsed/$reqCopyLimit*100)) : 0; @endphp
              <div style="height:100%;border-radius:8px;width:{{ $reqCopyPct }}%;background:{{ $reqCopyLeft<=0?'var(--red)':'var(--orange)' }}"></div>
            </div>
          </div>
          <div>
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px">
              <span style="font-size:.74rem;color:var(--gray600)"><i class="fa-solid fa-desktop" style="color:var(--g600);margin-right:4px"></i>Research/PC-Lab</span>
              <span style="font-size:.74rem;font-weight:800;color:{{ $reqMinLeft<=0?'var(--red)':'var(--g600)' }}">{{ $reqMinUsed }}/{{ $reqMinLimit }}m</span>
            </div>
            <div style="background:var(--gray100);border-radius:8px;height:6px;overflow:hidden">
              @php $reqMinPct = $reqMinLimit>0 ? min(100, round($reqMinUsed/$reqMinLimit*100)) : 0; @endphp
              <div style="height:100%;border-radius:8px;width:{{ $reqMinPct }}%;background:{{ $reqMinLeft<=0?'var(--red)':'var(--g500)' }}"></div>
            </div>
          </div>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:340px 1fr;gap:16px;align-items:start">

        {{-- LEFT: Request Info --}}
        <div>
          {{-- Request Card --}}
          <div class="profile-card">
            <div class="profile-card-hd"
              style="background:{{ $sr->service_type==='printing'?'var(--blue)':($sr->service_type==='photocopy'?'var(--orange)':'var(--g600)') }};color:#fff;border-radius:12px 12px 0 0;margin:-1px -1px 0">
              <i class="fa-solid {{ $sr->service_icon }}" style="color:#fff"></i>
              {{ ucfirst($sr->service_type) }} Request
              <span style="margin-left:auto;font-family:monospace;font-size:.8rem">{{ $sr->request_number }}</span>
            </div>
            <div class="profile-card-body" style="padding:0">
              @php
                $details = [
                  ['Request #',  $sr->request_number,           'fa-hashtag'],
                  ['Status',     strtoupper($sr->status),       'fa-circle-dot'],
                  ['Submitted',  $sr->created_at->format('M d, Y g:i A'), 'fa-calendar'],
                ];
                if ($sr->service_type === 'printing') {
                    $details[] = ['Paper Size', strtoupper($sr->paper_size), 'fa-expand'];
                    $details[] = ['Copies',     $sr->copies.' copy/copies',  'fa-hashtag'];
                    if ($sr->detected_pages) {
                        $details[] = ['Pages Detected', $sr->detected_pages.' page(s)', 'fa-file-lines'];
                        $details[] = ['Total Sheets',   ($sr->detected_pages * $sr->copies).' sheet(s) of '.strtoupper($sr->paper_size), 'fa-layer-group'];
                    }
                    $details[] = ['Print Type', ucfirst(str_replace('_',' ',$sr->print_type??'')), 'fa-palette'];
                    $details[] = ['File',       $sr->file_name, 'fa-file'];
                } elseif ($sr->service_type === 'photocopy') {
                  $details[] = ['Paper Size', strtoupper($sr->paper_size), 'fa-expand'];
                  $details[] = ['Copies',     $sr->copies.' copy/copies',  'fa-hashtag'];
                } else {
                  $details[] = ['Duration', $sr->duration_minutes.' minutes', 'fa-clock'];
                  if ($sr->computer) $details[] = ['PC Assigned', $sr->computer->name, 'fa-computer'];
                }
                $details[] = ['Purpose', $sr->purpose, 'fa-pen'];
                if ($sr->admin_note) $details[] = ['Admin Note', $sr->admin_note, 'fa-note-sticky'];
              @endphp
              @foreach($details as [$lbl,$val,$ico])
              <div style="padding:10px 18px;border-bottom:1px solid var(--gray100);display:flex;gap:10px;align-items:flex-start">
                <i class="fa-solid {{ $ico }}" style="color:var(--g600);width:14px;font-size:.78rem;margin-top:2px;flex-shrink:0"></i>
                <div>
                  <div style="font-size:.64rem;color:var(--gray400);font-weight:600;text-transform:uppercase">{{ $lbl }}</div>
                  <div style="font-size:.78rem;font-weight:600;color:var(--gray800);margin-top:1px">{{ $val }}</div>
                </div>
              </div>
              @endforeach

              @if($sr->service_type === 'printing' && $sr->file_path)
              <div style="padding:12px 18px">
                <a href="{{ Storage::url($sr->file_path) }}" target="_blank" class="btn" style="padding:9px;font-size:.78rem">
                  <i class="fa-solid fa-download"></i> Download File
                </a>
              </div>
              @endif
            </div>
          </div>

          {{-- Requester Info --}}
          <div class="profile-card" style="margin-top:14px">
            <div class="profile-card-hd"><i class="fa-solid fa-user"></i> Requester</div>
            <div class="profile-card-body" style="display:flex;align-items:center;gap:12px">
              <div class="sb-avatar" style="width:44px;height:44px;font-size:1rem;flex-shrink:0">
                @if($sr->user->profile_picture)<img src="{{ Storage::url($sr->user->profile_picture) }}">
                @else{{ strtoupper(substr($sr->user->first_name,0,1)) }}@endif
              </div>
              <div>
                <div style="font-size:.88rem;font-weight:800;color:var(--gray800)">{{ $sr->user->full_name }}</div>
                <div style="font-size:.72rem;color:var(--gray400)">{{ $sr->user->id_number }}</div>
                <div style="font-size:.72rem;color:var(--gray400)">{{ $sr->user->email }}</div>
                <div style="margin-top:5px">
                  <span class="tag {{ $sr->user->user_type==='student'?'tag-student':'tag-faculty' }}">
                    {{ ucfirst(str_replace('_',' ',$sr->user->user_type)) }}
                  </span>
                </div>
              </div>
            </div>
            <div style="padding:0 18px 14px">
              <a href="{{ route('admin.users.show', $sr->user) }}" class="btn" style="padding:8px;font-size:.76rem;background:linear-gradient(135deg,var(--gray600),var(--gray400))">
                <i class="fa-solid fa-eye"></i> View Full Profile
              </a>
            </div>
          </div>
        </div>

        {{-- RIGHT: Actions + Session Timer --}}
        <div>

          {{-- ACTION BUTTONS --}}
          <div class="profile-card" style="margin-bottom:14px">
            <div class="profile-card-hd"><i class="fa-solid fa-bolt"></i> Actions</div>
            <div class="profile-card-body" style="display:flex;flex-wrap:wrap;gap:8px">

              @if($sr->status === 'pending')
                <form action="{{ route('admin.service-requests.approve', $sr) }}" method="POST">
                  @csrf
                  <button class="btn" style="padding:9px 18px;font-size:.8rem;width:auto">
                    <i class="fa-solid fa-check"></i> Approve Request
                  </button>
                </form>
                <button class="btn" style="background:linear-gradient(135deg,var(--red),#c62828);padding:9px 18px;font-size:.8rem;width:auto"
                        onclick="openModal('rejectModal')">
                  <i class="fa-solid fa-xmark"></i> Reject Request
                </button>
              @endif

              @if($sr->status === 'approved' && $sr->service_type !== 'research')
                <form action="{{ route('admin.service-requests.processing', $sr) }}" method="POST">
                  @csrf
                  <button class="btn" style="background:linear-gradient(135deg,var(--blue),#1976d2);padding:9px 18px;font-size:.8rem;width:auto">
                    <i class="fa-solid fa-gear"></i> Mark as Processing
                  </button>
                </form>
              @endif

              @if($sr->status === 'approved' && $sr->service_type === 'research')
                <button class="btn" style="padding:9px 18px;font-size:.8rem;width:auto" onclick="openModal('assignPcModal')">
                  <i class="fa-solid fa-desktop"></i> Assign PC & Start Session
                </button>
              @endif

              @if($sr->status === 'processing' && $sr->service_type !== 'research')
                <form action="{{ route('admin.service-requests.complete', $sr) }}" method="POST">
                  @csrf
                  <button class="btn" style="padding:9px 18px;font-size:.8rem;width:auto">
                    <i class="fa-solid fa-check-double"></i> Mark as Completed
                  </button>
                </form>
              @endif

              @if($session && in_array($session->status, ['active','extended']))
                @if($reqMinLeft >= 15)
                  <button class="btn" style="background:linear-gradient(135deg,var(--orange),#f57c00);padding:9px 18px;font-size:.8rem;width:auto" onclick="openModal('extendModal')">
                    <i class="fa-solid fa-clock"></i> Extend Session
                  </button>
                @else
                  <button class="btn" type="button" disabled title="The user has less than 15 minutes remaining in today's daily limit"
                    style="background:var(--gray300);color:var(--gray600);padding:9px 18px;font-size:.8rem;width:auto;cursor:not-allowed;box-shadow:none">
                    <i class="fa-solid fa-ban"></i> Daily Limit Reached
                  </button>
                @endif
                <form action="{{ route('admin.service-requests.end-session', $sr) }}" method="POST"
                      onsubmit="return confirm('End session and mark as completed?')">
                  @csrf
                  <button class="btn" style="background:linear-gradient(135deg,var(--red),#c62828);padding:9px 18px;font-size:.8rem;width:auto">
                    <i class="fa-solid fa-stop"></i> End Session Now
                  </button>
                </form>
              @endif

            </div>
          </div>

          {{-- SESSION TIMER (Research only) --}}
          @if($sr->service_type === 'research' && $session)
          <div class="profile-card" style="margin-bottom:14px" id="session-timer-card">
            <div class="profile-card-hd">
              <i class="fa-solid fa-stopwatch" style="color:var(--g600)"></i> Live Session Timer
              <span id="session-status-badge" class="tag {{ in_array($session->status,['active','extended'])?'tag-active':'tag-done' }}" style="margin-left:auto">
                {{ strtoupper($session->status) }}
              </span>
            </div>
            <div class="profile-card-body" style="text-align:center">
              <div style="font-size:.78rem;color:var(--gray600);margin-bottom:6px">
                <i class="fa-solid fa-computer"></i> {{ $session->computer->name ?? '--' }} &nbsp;|&nbsp;
                <i class="fa-solid fa-clock"></i> Ends at <strong id="ends-at">{{ $session->ends_at?->format('g:i A') }}</strong>
                @if($session->extended_minutes)
                <span class="tag tag-pend" style="margin-left:6px">+{{ $session->extended_minutes }}min extended</span>
                @endif
              </div>

              {{-- Big countdown --}}
              <div id="countdown-display"
                style="font-size:3.5rem;font-weight:800;color:var(--g700);
                       font-variant-numeric:tabular-nums;letter-spacing:-2px;
                       margin:12px 0;line-height:1">
                --:--
              </div>
              <div style="font-size:.75rem;color:var(--gray400)">Time Remaining</div>

              {{-- Progress bar --}}
              <div style="background:var(--gray200);border-radius:10px;height:8px;margin:14px 0;overflow:hidden">
                <div id="progress-bar"
                     style="height:100%;border-radius:10px;background:linear-gradient(90deg,var(--g500),var(--g300));
                            transition:width 1s linear;width:100%"></div>
              </div>

              <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-top:14px;font-size:.72rem">
                <div style="background:var(--g100);border-radius:8px;padding:8px">
                  <div style="font-weight:700;color:var(--g700)">{{ $session->duration_minutes }}m</div>
                  <div style="color:var(--gray400)">Base Duration</div>
                </div>
                <div style="background:{{ $session->extended_minutes?'var(--orange-bg)':'var(--gray100)' }};border-radius:8px;padding:8px">
                  <div style="font-weight:700;color:{{ $session->extended_minutes?'var(--orange)':'var(--gray400)' }}">{{ $session->extended_minutes }}m</div>
                  <div style="color:var(--gray400)">Extended</div>
                </div>
                <div style="background:var(--blue-bg);border-radius:8px;padding:8px">
                  <div style="font-weight:700;color:var(--blue)">{{ $session->total_minutes }}m</div>
                  <div style="color:var(--gray400)">Total</div>
                </div>
              </div>
            </div>
          </div>
          @endif

          {{-- STATUS TIMELINE --}}
          <div class="profile-card">
            <div class="profile-card-hd"><i class="fa-solid fa-timeline"></i> Request Timeline</div>
            <div class="profile-card-body" style="padding:14px 18px">
              @php
                $steps = [
                  ['pending',    'Submitted',   $sr->created_at->format('M d, Y g:i A')],
                  ['approved',   'Approved',    $sr->reviewed_at?->format('M d, Y g:i A') ?? '—'],
                  ['processing', 'Processing',  $session?->started_at?->format('M d, Y g:i A') ?? '—'],
                  ['completed',  'Completed',   $sr->updated_at->format('M d, Y g:i A')],
                ];
                $order = ['pending'=>0,'approved'=>1,'processing'=>2,'completed'=>3,'rejected'=>1];
                $current = $order[$sr->status] ?? 0;
              @endphp
              <div style="display:flex;flex-direction:column;gap:0">
                @foreach($steps as $i => [$key,$label,$time])
                <div style="display:flex;gap:12px;align-items:flex-start;{{ !$loop->last?'padding-bottom:14px':'' }}">
                  <div style="display:flex;flex-direction:column;align-items:center;flex-shrink:0">
                    <div style="width:28px;height:28px;border-radius:50%;
                      background:{{ $i<=$current?'var(--g500)':'var(--gray200)' }};
                      display:flex;align-items:center;justify-content:center;
                      color:{{ $i<=$current?'#fff':'var(--gray400)' }};font-size:.72rem">
                      <i class="fa-solid {{ $i<$current?'fa-check':($i===$current?'fa-circle-dot':'fa-circle') }}"></i>
                    </div>
                    @if(!$loop->last)
                    <div style="width:2px;flex:1;min-height:14px;background:{{ $i<$current?'var(--g300)':'var(--gray200)' }};margin:3px 0"></div>
                    @endif
                  </div>
                  <div style="padding-top:4px">
                    <div style="font-size:.78rem;font-weight:{{ $i===$current?'800':'600' }};color:{{ $i<=$current?'var(--gray800)':'var(--gray400)' }}">{{ $label }}</div>
                    <div style="font-size:.68rem;color:var(--gray400);margin-top:1px">{{ $time }}</div>
                  </div>
                </div>
                @endforeach
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </main>
</div>

@push('styles')
<style>
.pc-opt{border:1.5px solid var(--gray200);border-radius:10px;padding:12px 8px;text-align:center;background:var(--white);transition:border-color .2s,background .2s}
input[type=radio]:checked + .pc-opt{border-color:var(--g500);background:var(--g100)}
.pc-opt:hover{border-color:var(--g400);background:var(--g50)}
.dur-opt{border:1.5px solid var(--gray200);border-radius:10px;padding:12px 8px;text-align:center;background:var(--white);transition:all .2s}
input[type=radio]:checked + .dur-opt{border-color:var(--g500);background:var(--g100)}
</style>
@endpush

@push('scripts')
<script>
function openModal(id){document.getElementById(id).classList.add('open')}
function closeModal(id){document.getElementById(id).classList.remove('open')}
document.querySelectorAll('.modal-bg').forEach(m=>m.addEventListener('click',e=>{if(e.target===m)m.classList.remove('open')}));

@if($errors->has('extend_minutes') && $session && in_array($session->status, ['active','extended']))
openModal('extendModal');
@endif

@if($session && in_array($session->status, ['active','extended']))
const SESSION_ID    = {{ $sr->id }};
const TOTAL_SECONDS = {{ $session->total_minutes * 60 }};
const PC_NAME       = '{{ $session->computer->name ?? "the PC" }}';
const USER_NAME     = '{{ $sr->user->first_name }} {{ $sr->user->last_name }}';
let remaining       = {{ $session->remaining_seconds }};
let alarmPlayed     = false;
let warningPlayed   = false;

function playBeep(freq=880, dur=0.4, vol=0.5) {
  try {
    const ctx  = new (window.AudioContext||window.webkitAudioContext)();
    const osc  = ctx.createOscillator();
    const gain = ctx.createGain();
    osc.connect(gain); gain.connect(ctx.destination);
    osc.type = 'sine'; osc.frequency.value = freq;
    gain.gain.setValueAtTime(vol, ctx.currentTime);
    gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + dur);
    osc.start(ctx.currentTime); osc.stop(ctx.currentTime + dur);
  } catch(e){}
}

function speakAlert(text) {
  if ('speechSynthesis' in window) {
    window.speechSynthesis.cancel();
    const u = new SpeechSynthesisUtterance(text);
    u.lang   = 'en-US';
    u.rate   = 0.88;
    u.pitch  = 1;
    u.volume = 1;
    // Pick a clear voice if available
    const voices = window.speechSynthesis.getVoices();
    const english = voices.find(v => v.lang.startsWith('en') && !v.name.includes('Google') === false);
    if (english) u.voice = english;
    window.speechSynthesis.speak(u);
  }
}

function playAlarm() {
  playBeep(880, 0.3);
  setTimeout(()=>playBeep(660, 0.3), 350);
  setTimeout(()=>playBeep(440, 0.6), 700);
}

function playWarning() {
  playBeep(660, 0.25);
  setTimeout(()=>playBeep(660, 0.25), 300);
}

function formatTime(s) {
  const m = Math.floor(s/60), sec = s%60;
  return String(m).padStart(2,'0') + ':' + String(sec).padStart(2,'0');
}

function updateTimer() {
  if (remaining < 0) remaining = 0;

  const el  = document.getElementById('countdown-display');
  const bar = document.getElementById('progress-bar');

  if (el) {
    el.textContent = formatTime(remaining);
    if (remaining <= 60)        el.style.color = 'var(--red)';
    else if (remaining <= 300)  el.style.color = 'var(--orange)';
    else                        el.style.color = 'var(--g700)';
  }

  if (bar) {
    const pct = TOTAL_SECONDS > 0 ? (remaining / TOTAL_SECONDS * 100) : 0;
    bar.style.width = pct + '%';
    if (remaining <= 60)       bar.style.background = 'linear-gradient(90deg,var(--red),#ff6b6b)';
    else if (remaining <= 300) bar.style.background = 'linear-gradient(90deg,var(--orange),#ffb74d)';
    else                       bar.style.background = 'linear-gradient(90deg,var(--g500),var(--g300))';
  }

  // 5-minute warning
  if (remaining <= 300 && !warningPlayed) {
    warningPlayed = true;
    playWarning();
    speakAlert(`Attention. ${USER_NAME} has 5 minutes remaining on ${PC_NAME}.`);
    showToast(`⚠️ ${USER_NAME} has 5 minutes remaining on ${PC_NAME}!`, 'warn');
  }

  // Time's up
  if (remaining <= 0 && !alarmPlayed) {
    alarmPlayed = true;
    playAlarm();
    speakAlert(`Time is up. ${USER_NAME} on ${PC_NAME} has exceeded their session time. Please assist the user.`);
    showToast(`🔔 Time's up! ${USER_NAME} on ${PC_NAME}`, 'err');
    const badge = document.getElementById('session-status-badge');
    if (badge) { badge.textContent = 'EXPIRED'; badge.className = 'tag tag-rej'; }
  }

  if (remaining > 0) { remaining--; setTimeout(updateTimer, 1000); }
}

// Sync every 30s
function syncTimer() {
  fetch('{{ route("admin.service-requests.session-status", $sr) }}')
    .then(r=>r.json())
    .then(d=>{
      remaining = d.remaining_seconds;
      const ea = document.getElementById('ends-at');
      if (ea) ea.textContent = d.ends_at;
    }).catch(()=>{});
}

// Load voices before starting
window.speechSynthesis.onvoiceschanged = () => { window.speechSynthesis.getVoices(); };
window.speechSynthesis.getVoices();

updateTimer();
setInterval(syncTimer, 30000);

function showToast(msg, type='ok') {
  const c = document.getElementById('toast-container') || (() => {
    const d = document.createElement('div');
    d.id = 'toast-container';
    d.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999;display:flex;flex-direction:column;gap:8px;max-width:340px';
    document.body.appendChild(d);
    return d;
  })();
  const t = document.createElement('div');
  const colors = {ok:'var(--g500)',warn:'var(--orange)',err:'var(--red)',info:'var(--blue)'};
  t.style.cssText=`background:#fff;border-radius:12px;box-shadow:0 8px 24px rgba(0,0,0,.15);padding:14px 16px;border-left:4px solid ${colors[type]||colors.info};animation:fadeUp .3s ease;display:flex;gap:10px;align-items:flex-start`;
  t.innerHTML=`<i class="fa-solid fa-bell" style="color:${colors[type]};margin-top:2px;flex-shrink:0"></i><div style="flex:1"><div style="font-size:.8rem;font-weight:800;color:var(--gray800)">${msg}</div></div><button onclick="this.closest('div[style]').remove()" style="background:none;border:none;color:var(--gray400);cursor:pointer">✕</button>`;
  c.appendChild(t);
  setTimeout(()=>{ if(t.parentNode){t.style.opacity='0';setTimeout(()=>t.remove(),300);} },6000);
}
@endif
</script>
@endpush
@endsection