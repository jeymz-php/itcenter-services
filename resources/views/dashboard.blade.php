@extends('layouts.app')
@section('title','Dashboard | IT Center Services')
@section('body-class','dash-page')
@section('content')


<div class="dash-wrap">
  @include('user.partials.sidebar')
  <main class="main">
    <div class="topbar">
      <div>
        <h1>
          @if($user->status==='pending') Account Pending Verification
          @elseif($user->status==='deactivated') Account Deactivated
          @elseif($user->status==='rejected') Account Rejected
          @else {{ ucfirst(str_replace('_',' ',$user->user_type)) }} Dashboard
          @endif
        </h1>
        <p>Welcome back, {{ $user->first_name }} {{ $user->last_name }}!</p>
      </div>
      <div class="topbar-right">
        @include('user.partials.notification-button')
        <div class="clock">
          <i class="fa-solid fa-clock" style="color:var(--g600)"></i>
          <span id="clock">--:-- --</span>
        </div>
      </div>
    </div>

    <div class="content">

      @if(session('success'))
        <div class="abox ok" style="margin-bottom:16px">
          <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
      @endif

      @if(session('warning'))
        <div class="abox warn" style="margin-bottom:16px">
          <i class="fa-solid fa-triangle-exclamation"></i> {{ session('warning') }}
        </div>
      @endif

      @if($errors->any())
        <div class="abox err" style="margin-bottom:16px">
          <i class="fa-solid fa-triangle-exclamation"></i>
          <div>@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
        </div>
      @endif

      @if(!$user->guide_seen_at)
      <div class="abox warn" data-first-guide-note style="margin-bottom:16px;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;padding:14px 16px">
        <div style="display:flex;align-items:flex-start;gap:10px;flex:1;min-width:240px">
          <i class="fa-solid fa-book-open" style="margin-top:2px"></i>
          <div>
            <strong>First time using IT Center Services?</strong><br>
            Please open the <strong>User Guide</strong> first to review the User Manual and Infographics before submitting your first request.
          </div>
        </div>
        <button type="button" onclick="openUserGuide('manual')"
          style="border:none;border-radius:8px;background:var(--g700);color:#fff;padding:9px 14px;font-family:inherit;font-size:.75rem;font-weight:800;cursor:pointer;display:flex;align-items:center;gap:7px;white-space:nowrap">
          <i class="fa-solid fa-book-open-reader"></i> Open User Guide
        </button>
      </div>
      @endif

      {{-- ── PENDING STATE ── --}}
      @if($user->status === 'pending')
      <div class="verify-state">
        <div class="vi" style="background:var(--orange-bg);color:var(--orange)">
          <i class="fa-solid fa-hourglass-half"></i>
        </div>
        <h3>Your Account is Pending Verification</h3>
        <p>Your registration was successful. An IT Center administrator will review and verify your account shortly.</p>
        <div style="display:flex;gap:10px;flex-wrap:wrap;justify-content:center;margin-top:20px">
          <div style="background:var(--g100);border-radius:10px;padding:14px 20px;text-align:center;min-width:120px">
            <div style="font-size:1.3rem;margin-bottom:4px">📋</div>
            <div style="font-size:.73rem;font-weight:700;color:var(--g700)">Status</div>
            <div style="font-size:.76rem;color:var(--gray600)">Under Review</div>
          </div>
          <div style="background:var(--g100);border-radius:10px;padding:14px 20px;text-align:center;min-width:120px">
            <div style="font-size:1.3rem;margin-bottom:4px">⏱️</div>
            <div style="font-size:.73rem;font-weight:700;color:var(--g700)">Est. Time</div>
            <div style="font-size:.76rem;color:var(--gray600)">1–2 Business Days</div>
          </div>
          <div style="background:var(--g100);border-radius:10px;padding:14px 20px;text-align:center;min-width:120px">
            <div style="font-size:1.3rem;margin-bottom:4px">📧</div>
            <div style="font-size:.73rem;font-weight:700;color:var(--g700)">Notification</div>
            <div style="font-size:.76rem;color:var(--gray600)">Via Email</div>
          </div>
        </div>
        <div class="abox info" style="max-width:420px;margin-top:20px;text-align:left">
          <i class="fa-solid fa-circle-info"></i>
          <div>For faster verification, visit the IT Center with your valid UCC ID.<br>
          <strong>itcenter@ucc-caloocan.edu.ph</strong></div>
        </div>

        <div class="pending-refresh-panel">
          <div class="pending-refresh-note">
            <i class="fa-solid fa-arrows-rotate"></i>
            <div><strong>Already approved by an administrator?</strong><br>Click the Refresh Status button to reload your account and check whether it is still pending or already active.</div>
          </div>
          <a href="{{ route('dashboard') }}" class="btn pending-refresh-btn" data-loading-message="Refreshing account status...">
            <i class="fa-solid fa-rotate"></i> Refresh Status
          </a>
        </div>
      </div>

      {{-- ── DEACTIVATED STATE ── --}}
      @elseif($user->status === 'deactivated')
      <div class="verify-state">
        <div class="vi" style="background:#f3e5f5;color:#7b1fa2">
          <i class="fa-solid fa-user-slash"></i>
        </div>
        <h3>Your Account Has Been Deactivated</h3>
        <p>Your account is currently deactivated. You cannot access IT Center services at this time.</p>
        <a href="{{ route('profile') }}" class="btn" style="margin-top:20px;max-width:260px">
          <i class="fa-solid fa-rotate-left"></i> Request Reactivation
        </a>
      </div>

      {{-- ── REJECTED STATE ── --}}
      @elseif($user->status === 'rejected')
      <div class="verify-state">
        <div class="vi" style="background:var(--red-bg);color:var(--red)">
          <i class="fa-solid fa-user-xmark"></i>
        </div>
        <h3>Account Registration Rejected</h3>
        <p>Your account registration was not approved. Contact the IT Center for more information.</p>
        <div class="abox err" style="max-width:380px;margin-top:16px;text-align:left">
          <i class="fa-solid fa-envelope"></i>
          <div>Contact: <strong>itcenter@ucc-caloocan.edu.ph</strong></div>
        </div>
      </div>

      {{-- ── ACTIVE DASHBOARD ── --}}
      @else

      @if($unavailableServices->isNotEmpty())
      <div class="abox warn" style="margin-bottom:16px">
        <i class="fa-solid fa-circle-exclamation"></i>
        <div><strong>Temporarily unavailable:</strong> {{ $unavailableServices->join(', ') }}. Existing requests remain visible. Please review the User Manual or Infographics and check again later.</div>
      </div>
      @endif

      <div class="abox {{ $systemOpenNow ? 'info' : 'warn' }}" style="margin-bottom:16px">
        <i class="fa-solid {{ $systemOpenNow ? 'fa-door-open' : 'fa-clock' }}"></i>
        <div><strong>{{ $systemOpenNow ? 'IT Center is open for new requests.' : 'IT Center is currently closed for new requests.' }}</strong> {{ $todayHours }}.</div>
      </div>

      {{-- ACTIVE PC SESSION BANNER --}}
      @if($activeSession)
      <div id="active-pc-session-card" data-active-session-card data-request-id="{{ $activeSession->service_request_id }}" style="background:linear-gradient(135deg,var(--g700),var(--g500));border-radius:14px;padding:18px 20px;margin-bottom:18px;color:#fff;transition:all .3s ease">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
          <div style="display:flex;align-items:center;gap:14px">
            <div style="width:48px;height:48px;border-radius:12px;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0">
              <i class="fa-solid fa-desktop"></i>
            </div>
            <div>
              <div style="font-size:.9rem;font-weight:800;margin-bottom:3px">
                Active PC Session — {{ $activeSession->computer->name }}
              </div>
              <div style="font-size:.75rem;opacity:.85">
                Request {{ $activeSession->serviceRequest->request_number }} ·
                Started {{ $activeSession->started_at->format('g:i A') }} ·
                Ends <strong id="session-ends">{{ $activeSession->ends_at->format('g:i A') }}</strong>
              </div>
            </div>
          </div>
          <div style="display:flex;align-items:center;gap:12px">
            <div style="text-align:center">
              <div id="session-countdown" style="font-size:1.6rem;font-weight:800;font-variant-numeric:tabular-nums;letter-spacing:-1px">--:--</div>
              <div style="font-size:.65rem;opacity:.7">remaining</div>
            </div>
            <button onclick="openModal('extendRequestModal')"
              style="background:rgba(255,255,255,.2);border:1.5px solid rgba(255,255,255,.4);color:#fff;border-radius:9px;padding:8px 16px;font-size:.78rem;font-weight:700;cursor:pointer;white-space:nowrap">
              <i class="fa-solid fa-clock"></i> Request Extension
            </button>
          </div>
        </div>
        <div style="background:rgba(255,255,255,.2);border-radius:6px;height:6px;margin-top:14px;overflow:hidden">
          <div id="session-progress" style="height:100%;border-radius:6px;background:rgba(255,255,255,.8);transition:width 1s linear;width:100%"></div>
        </div>
      </div>

      {{-- EXTEND SESSION REQUEST MODAL --}}
      <div class="modal-bg" id="extendRequestModal">
        <div class="modal-box">
          <div class="modal-hd">
            <h3><i class="fa-solid fa-clock" style="color:var(--g600);margin-right:6px"></i>Request Session Extension</h3>
            <button class="modal-close" onclick="closeModal('extendRequestModal')"><i class="fa-solid fa-xmark"></i></button>
          </div>
          <form action="{{ route('requests.request-extend', $activeSession->serviceRequest) }}" method="POST">
            @csrf
            <div class="modal-body">
              <div class="abox info" style="margin-bottom:14px">
                <i class="fa-solid fa-circle-info"></i>
                <div>Your extension request will be sent to the IT Center admin for approval. You will be notified once approved.</div>
              </div>
              <div class="fg">
                <div class="flabel">Extend By</div>
                <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:8px">
                  @foreach([15,30,45,60] as $min)
                  <label style="cursor:pointer">
                    <input type="radio" name="extend_minutes" value="{{ $min }}" style="display:none" required>
                    <div class="dur-opt" style="border:1.5px solid var(--gray200);border-radius:10px;padding:12px 6px;text-align:center;background:var(--white);transition:all .2s">
                      <div style="font-size:1.1rem;font-weight:800;color:var(--g700)">{{ $min }}</div>
                      <div style="font-size:.65rem;color:var(--gray400)">minutes</div>
                    </div>
                  </label>
                  @endforeach
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="modal-btn secondary" onclick="closeModal('extendRequestModal')">Cancel</button>
              <button type="submit" class="modal-btn primary"><i class="fa-solid fa-paper-plane"></i> Send Request</button>
            </div>
          </form>
        </div>
      </div>
      @endif

      {{-- RATING PROMPT MODAL: one appearance per completed request --}}
      @if($pendingRating)
      <div class="modal-bg rating-review-bg" id="ratingModal" data-static-modal="true" role="dialog" aria-modal="true" aria-labelledby="ratingModalTitle">
        <div class="modal-box rating-review-modal">
          <div class="rating-review-hero">
            <div class="rating-review-icon"><i class="fa-solid fa-star"></i></div>
            <div>
              <div class="rating-review-kicker">SERVICE COMPLETED</div>
              <h3 id="ratingModalTitle">How was your experience?</h3>
              <p>Your {{ ucfirst($pendingRating->service_type) }} request <strong>{{ $pendingRating->request_number }}</strong> is complete. This review prompt appears only once for this request.</p>
            </div>
          </div>

          <form action="{{ route('ratings.store') }}" method="POST" data-loading-message="Submitting your feedback...">
            @csrf
            <input type="hidden" name="service_request_id" value="{{ $pendingRating->id }}">
            <input type="hidden" name="stars" id="stars-input" value="0">
            <div class="modal-body rating-review-body" tabindex="0">
              <div class="rating-section-label">Review visibility</div>
              <div class="rating-visibility-grid">
                <label class="rating-visibility-card">
                  <input type="radio" name="visibility" value="public" checked>
                  <span class="rating-radio-dot"></span>
                  <span><strong>Public details</strong><small>Show your full name, ID number and campus.</small></span>
                </label>
                <label class="rating-visibility-card">
                  <input type="radio" name="visibility" value="anonymous">
                  <span class="rating-radio-dot"></span>
                  <span><strong>Anonymous</strong><small>Mask your name and most of your ID number.</small></span>
                </label>
              </div>

              <div class="rating-stars-panel">
                <div class="rating-section-label">Overall rating <span style="color:var(--red)">*</span></div>
                <div id="star-selector" class="rating-star-selector" role="radiogroup" aria-label="Star rating">
                  @for($i=1;$i<=5;$i++)
                    <button type="button" class="rating-star-button" data-value="{{ $i }}" aria-label="{{ $i }} star{{ $i>1?'s':'' }}">
                      <i class="fa-regular fa-star"></i>
                    </button>
                  @endfor
                </div>
                <div id="rating-star-label" class="rating-star-label">Select from 1 to 5 stars</div>
              </div>

              <div class="fg">
                <div class="flabel"><i class="fa-solid fa-comment-dots"></i> Comment <span class="rating-optional">Optional</span></div>
                <textarea name="comment" class="fc" rows="3" maxlength="1000" placeholder="Tell us what went well or what could be improved..."></textarea>
              </div>

              <div class="fg" style="margin-bottom:0">
                <div class="flabel"><i class="fa-solid fa-lightbulb"></i> Suggestions or questions <span class="rating-optional">Optional</span></div>
                <textarea name="suggestions" class="fc" rows="3" maxlength="1000" placeholder="Share an idea for improving the IT Center Services system..."></textarea>
              </div>
            </div>
            <div class="modal-footer rating-review-footer">
              <button type="button" id="rating-maybe-later" class="modal-btn secondary" onclick="dismissRatingPrompt()">
                <i class="fa-regular fa-clock"></i> Maybe Later
              </button>
              <button type="submit" class="modal-btn primary">
                <i class="fa-solid fa-paper-plane"></i> Submit Review
              </button>
            </div>
          </form>
        </div>
      </div>
      @endif
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:18px">
        <div style="background:var(--white);border-radius:14px;border:1.5px solid var(--gray200);padding:16px 18px;box-shadow:var(--shadow-sm)">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
            <div style="font-size:.78rem;font-weight:800;color:var(--gray800)"><i class="fa-solid fa-print" style="color:var(--blue);margin-right:5px"></i>Printing Today</div>
            <div style="font-size:.75rem;font-weight:800;color:{{ $printingRemainingToday<=0?'var(--red)':'var(--blue)' }}">{{ $printingUsedToday }}/{{ $printingLimit }}</div>
          </div>
          <div style="background:var(--gray100);border-radius:8px;height:8px;overflow:hidden">
            @php $printPct = $printingLimit>0 ? min(100, round($printingUsedToday/$printingLimit*100)) : 0; @endphp
            <div style="height:100%;border-radius:8px;width:{{ $printPct }}%;background:{{ $printingRemainingToday<=0?'var(--red)':'var(--blue)' }}"></div>
          </div>
          <div style="font-size:.68rem;color:var(--gray400);margin-top:6px">
            @if($printingRemainingToday > 0) {{ $printingRemainingToday }} page(s) left today @else Limit reached — resets 12:00 AM @endif
          </div>
        </div>
        <div style="background:var(--white);border-radius:14px;border:1.5px solid var(--gray200);padding:16px 18px;box-shadow:var(--shadow-sm)">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
            <div style="font-size:.78rem;font-weight:800;color:var(--gray800)"><i class="fa-solid fa-copy" style="color:var(--orange);margin-right:5px"></i>Photocopy Today</div>
            <div style="font-size:.75rem;font-weight:800;color:{{ $photocopyRemainingToday<=0?'var(--red)':'var(--orange)' }}">{{ $photocopyUsedToday }}/{{ $photocopyLimit }}</div>
          </div>
          <div style="background:var(--gray100);border-radius:8px;height:8px;overflow:hidden">
            @php $copyPct = $photocopyLimit>0 ? min(100, round($photocopyUsedToday/$photocopyLimit*100)) : 0; @endphp
            <div style="height:100%;border-radius:8px;width:{{ $copyPct }}%;background:{{ $photocopyRemainingToday<=0?'var(--red)':'var(--orange)' }}"></div>
          </div>
          <div style="font-size:.68rem;color:var(--gray400);margin-top:6px">
            @if($photocopyRemainingToday > 0) {{ $photocopyRemainingToday }} page(s) left today @else Limit reached — resets 12:00 AM @endif
          </div>
        </div>
        <div style="background:var(--white);border-radius:14px;border:1.5px solid var(--gray200);padding:16px 18px;box-shadow:var(--shadow-sm)">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
            <div style="font-size:.78rem;font-weight:800;color:var(--gray800)"><i class="fa-solid fa-desktop" style="color:var(--g600);margin-right:5px"></i>Research/PC-Lab Today</div>
            <div style="font-size:.75rem;font-weight:800;color:{{ $minutesRemainingToday<=0?'var(--red)':'var(--g600)' }}">{{ $minutesUsedToday }}/{{ $minutesLimit }}m</div>
          </div>
          <div style="background:var(--gray100);border-radius:8px;height:8px;overflow:hidden">
            @php $minPct = $minutesLimit>0 ? min(100, round($minutesUsedToday/$minutesLimit*100)) : 0; @endphp
            <div style="height:100%;border-radius:8px;width:{{ $minPct }}%;background:{{ $minutesRemainingToday<=0?'var(--red)':'var(--g500)' }}"></div>
          </div>
          <div style="font-size:.68rem;color:var(--gray400);margin-top:6px">
            @if($minutesRemainingToday > 0) {{ $minutesRemainingToday }} minute(s) left today @else Limit reached — resets 12:00 AM @endif
          </div>
        </div>
      </div>

      {{-- STAT CARDS --}}
      <div class="stat-grid" style="grid-template-columns:repeat(5,1fr)">
        <div class="stat-card" style="border-color:var(--orange-bg)">
          <div class="stat-ico" style="background:var(--orange-bg);color:var(--orange)"><i class="fa-solid fa-hourglass-half"></i></div>
          <div><div class="stat-lbl">Pending</div><div class="stat-val">{{ $stats['pending'] }}</div></div>
        </div>
        <div class="stat-card" style="border-color:var(--blue-bg)">
          <div class="stat-ico" style="background:var(--blue-bg);color:var(--blue)"><i class="fa-solid fa-circle-check"></i></div>
          <div><div class="stat-lbl">Approved</div><div class="stat-val">{{ $stats['approved'] }}</div></div>
        </div>
        <div class="stat-card" style="border-color:var(--g100)">
          <div class="stat-ico" style="background:var(--g100);color:var(--g600)"><i class="fa-solid fa-gear"></i></div>
          <div><div class="stat-lbl">Processing</div><div class="stat-val">{{ $stats['processing'] }}</div></div>
        </div>
        <div class="stat-card" style="border-color:var(--g200)">
          <div class="stat-ico" style="background:var(--g100);color:var(--g700)"><i class="fa-solid fa-check-double"></i></div>
          <div><div class="stat-lbl">Completed</div><div class="stat-val">{{ $stats['completed'] }}</div></div>
        </div>
        <div class="stat-card" style="border-color:var(--purple-bg)">
          <div class="stat-ico" style="background:var(--purple-bg);color:var(--purple)"><i class="fa-solid fa-list-check"></i></div>
          <div><div class="stat-lbl">Total</div><div class="stat-val">{{ $stats['total'] }}</div></div>
        </div>
      </div>

      {{-- QUICK ACTIONS --}}
      <div class="qa-grid" style="margin-bottom:20px">
        <a href="{{ route('requests.printing') }}" class="qa-card" style="border-color:var(--blue-bg);{{ !($serviceAvailability['printing'] ?? true) ? 'opacity:.55' : '' }}">
          <div class="qa-ico" style="color:var(--blue)"><i class="fa-solid fa-print"></i></div>
          <div class="qa-lbl" style="color:var(--blue)">Printing</div>
          @if(!($serviceAvailability['printing'] ?? true))<span class="tag tag-rej" style="font-size:.58rem">Unavailable</span>@endif
        </a>
        <a href="{{ route('requests.photocopy') }}" class="qa-card" style="border-color:var(--orange-bg);{{ !($serviceAvailability['photocopy'] ?? true) ? 'opacity:.55' : '' }}">
          <div class="qa-ico" style="color:var(--orange)"><i class="fa-solid fa-copy"></i></div>
          <div class="qa-lbl" style="color:var(--orange)">Photocopy</div>
          @if(!($serviceAvailability['photocopy'] ?? true))<span class="tag tag-rej" style="font-size:.58rem">Unavailable</span>@endif
        </a>
        <a href="{{ route('requests.research') }}" class="qa-card" style="border-color:var(--g100);{{ !($serviceAvailability['research'] ?? true) ? 'opacity:.55' : '' }}">
          <div class="qa-ico" style="color:var(--g600)"><i class="fa-solid fa-desktop"></i></div>
          <div class="qa-lbl" style="color:var(--g600)">Research</div>
          @if(!($serviceAvailability['research'] ?? true))<span class="tag tag-rej" style="font-size:.58rem">Unavailable</span>@endif
        </a>
        <a href="{{ route('requests.history') }}" class="qa-card" style="border-color:var(--orange-bg)">
          <div class="qa-ico" style="color:#b86a00"><i class="fa-solid fa-clock-rotate-left"></i></div>
          <div class="qa-lbl" style="color:#b86a00">My Requests</div>
        </a>
      </div>

      {{-- RECENT REQUESTS --}}
      <div class="section-hd">
        <h3><i class="fa-solid fa-rectangle-list" style="color:var(--g600)"></i> Recent Requests</h3>
        <a href="{{ route('requests.history') }}">View All →</a>
      </div>
      <div class="tbl-wrap">
        <table>
          <thead>
            <tr><th>REQUEST #</th><th>SERVICE</th><th>DETAILS</th><th>DATE</th><th>STATUS</th></tr>
          </thead>
          <tbody>
            @forelse($recentRequests as $r)
            <tr>
              <td style="font-family:monospace;font-weight:700;font-size:.75rem">{{ $r->request_number }}</td>
              <td>
                @php
                  $bg = $r->service_type==='printing'?'var(--blue-bg)':($r->service_type==='photocopy'?'var(--orange-bg)':'var(--g100)');
                  $cl = $r->service_type==='printing'?'var(--blue)':($r->service_type==='photocopy'?'var(--orange)':'var(--g600)');
                  $ic = $r->service_type==='printing'?'fa-print':($r->service_type==='photocopy'?'fa-copy':'fa-desktop');
                @endphp
                <span class="tag" style="background:{{ $bg }};color:{{ $cl }}">
                  <i class="fa-solid {{ $ic }}"></i> {{ ucfirst($r->service_type) }}
                </span>
              </td>
              <td style="font-size:.74rem;color:var(--gray600)">
                @if($r->service_type==='printing') {{ $r->copies }}x · {{ strtoupper($r->paper_size) }}
                @elseif($r->service_type==='photocopy') {{ $r->copies }}x · {{ strtoupper($r->paper_size) }}
                @else {{ $r->duration_minutes }} min PC use
                @endif
              </td>
              <td style="font-size:.72rem;color:var(--gray600)">{{ $r->created_at->format('M d, Y') }}</td>
              <td>
                @php $sc=['pending'=>'tag-pend','approved'=>'tag-appr','processing'=>'tag-res','completed'=>'tag-done','rejected'=>'tag-rej'] @endphp
                <span class="tag {{ $sc[$r->status]??'tag-arch' }}" data-request-status-id="{{ $r->id }}" data-current-status="{{ $r->status }}">{{ strtoupper($r->status) }}</span>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="5" style="text-align:center;padding:28px;color:var(--gray400)">
                <i class="fa-solid fa-inbox" style="display:block;font-size:1.5rem;margin-bottom:8px"></i>
                No requests yet.
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      @endif {{-- end active --}}
    </div>
  </main>
</div>

@push('styles')
<style>
.stat-grid{display:grid;gap:12px;margin-bottom:18px}
input[type=radio]:checked+.dur-opt{border-color:var(--g500)!important;background:var(--g100)!important}

.pending-refresh-panel{max-width:500px;margin-top:18px;padding:15px;border:1.5px solid var(--g200);border-radius:13px;background:var(--g50)}
.pending-refresh-note{display:flex;gap:10px;text-align:left;font-size:.73rem;line-height:1.55;color:var(--gray600);margin-bottom:12px}.pending-refresh-note>i{color:var(--g600);margin-top:3px}.pending-refresh-btn{max-width:220px;margin:0 auto;padding:10px 16px;text-decoration:none}
.rating-review-bg{background:rgba(7,31,22,.68);backdrop-filter:blur(6px);overflow:hidden;overscroll-behavior:none;padding:12px;align-items:center;justify-content:center}
.rating-review-modal{width:100%;max-width:570px;height:calc(100vh - 24px);height:calc(100dvh - 24px);max-height:760px;border-radius:20px;overflow:hidden;margin:0;display:flex;flex-direction:column;box-sizing:border-box}
.rating-review-modal>form{display:flex;flex:1 1 auto;width:100%;height:100%;min-height:0;overflow:hidden;flex-direction:column}
.rating-review-hero{padding:22px 24px;display:flex;gap:14px;align-items:flex-start;color:#fff;background:linear-gradient(135deg,#124530,#249660);flex:0 0 auto}
.rating-review-icon{width:52px;height:52px;border-radius:15px;display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,.16);font-size:1.25rem;flex-shrink:0;color:#ffd66b}
.rating-review-kicker{font-size:.62rem;font-weight:800;letter-spacing:.12em;color:rgba(255,255,255,.72);margin-bottom:4px}.rating-review-hero h3{font-size:1.14rem;margin:0 0 5px}.rating-review-hero p{font-size:.72rem;line-height:1.55;color:rgba(255,255,255,.78);margin:0}
.rating-review-body{padding:20px 22px;flex:1 1 auto;min-height:0;overflow-y:auto!important;overflow-x:hidden;-webkit-overflow-scrolling:touch;overscroll-behavior:contain;scrollbar-gutter:stable}.rating-section-label{font-size:.72rem;font-weight:800;color:var(--gray700);margin-bottom:8px}
.rating-visibility-grid{display:grid;grid-template-columns:1fr 1fr;gap:9px;margin-bottom:17px}.rating-visibility-card{position:relative;display:flex;gap:9px;align-items:flex-start;border:1.5px solid var(--gray200);border-radius:11px;padding:11px;cursor:pointer;background:var(--white);transition:.18s}.rating-visibility-card:hover{border-color:var(--g300)}.rating-visibility-card input{position:absolute;opacity:0}.rating-visibility-card:has(input:checked){border-color:var(--g500);background:var(--g50);box-shadow:0 0 0 2px rgba(36,150,96,.08)}.rating-radio-dot{width:16px;height:16px;border:2px solid var(--gray300);border-radius:50%;margin-top:1px;flex-shrink:0;position:relative}.rating-visibility-card input:checked~.rating-radio-dot{border-color:var(--g600)}.rating-visibility-card input:checked~.rating-radio-dot:after{content:'';position:absolute;inset:3px;border-radius:50%;background:var(--g600)}.rating-visibility-card strong{display:block;font-size:.75rem;color:var(--gray800)}.rating-visibility-card small{display:block;font-size:.63rem;line-height:1.45;color:var(--gray400);margin-top:2px}
.rating-stars-panel{border:1.5px solid #ffe0a3;background:#fffaf0;border-radius:12px;padding:14px;text-align:center;margin-bottom:16px}.rating-star-selector{display:flex;justify-content:center;gap:5px}.rating-star-button{border:0;background:transparent;color:#d4d9d7;font-size:1.9rem;cursor:pointer;padding:2px 4px;transition:transform .15s,color .15s}.rating-star-button:hover{transform:scale(1.12)}.rating-star-button.active{color:#f5a623}.rating-star-label{font-size:.65rem;color:#8b6a24;margin-top:5px;font-weight:700}.rating-optional{font-size:.6rem;font-weight:600;color:var(--gray400);margin-left:auto}.rating-review-footer{justify-content:space-between;background:var(--gray50);flex:0 0 auto}
body.rating-modal-open{overflow:hidden!important}
@media(max-width:600px){.rating-review-bg{padding:8px;align-items:flex-start}.rating-review-modal{height:calc(100vh - 16px);height:calc(100dvh - 16px);max-height:none;border-radius:16px}.rating-visibility-grid{grid-template-columns:1fr}.rating-review-hero{padding:16px}.rating-review-icon{width:46px;height:46px;border-radius:13px}.rating-review-body{padding:16px}.rating-review-footer{padding:12px 16px;flex-direction:column-reverse}.rating-review-footer .modal-btn{width:100%}}
@media(max-height:700px){.rating-review-hero{padding-top:14px;padding-bottom:14px}.rating-review-hero p{line-height:1.4}.rating-review-body{padding-top:15px;padding-bottom:15px}}
</style>
@endpush

@push('scripts')
<script>
function openModal(id){document.getElementById(id).classList.add('open')}
function closeModal(id){document.getElementById(id).classList.remove('open')}
document.querySelectorAll('.modal-bg').forEach(m=>m.addEventListener('click',e=>{if(e.target===m && m.dataset.staticModal!=='true')m.classList.remove('open')}));

@if($pendingRating)
document.addEventListener('DOMContentLoaded', () => {
  document.body.classList.add('rating-modal-open');
  openModal('ratingModal');
  const reviewBody = document.querySelector('#ratingModal .rating-review-body');
  if (reviewBody) reviewBody.scrollTop = 0;
});

const ratingLabels = ['', 'Poor', 'Fair', 'Good', 'Very good', 'Excellent'];
document.querySelectorAll('#star-selector .rating-star-button').forEach(button => {
  button.addEventListener('click', () => {
    const value = parseInt(button.dataset.value, 10);
    document.getElementById('stars-input').value = value;
    document.getElementById('rating-star-label').textContent = ratingLabels[value];
    document.querySelectorAll('#star-selector .rating-star-button').forEach(star => {
      const active = parseInt(star.dataset.value, 10) <= value;
      star.classList.toggle('active', active);
      star.querySelector('i').className = active ? 'fa-solid fa-star' : 'fa-regular fa-star';
      star.setAttribute('aria-checked', parseInt(star.dataset.value, 10) === value ? 'true' : 'false');
    });
  });
});

document.querySelector('#ratingModal form')?.addEventListener('submit', (e) => {
  if (parseInt(document.getElementById('stars-input').value, 10) < 1) {
    e.preventDefault();
    document.getElementById('rating-star-label').textContent = 'Please select a star rating first.';
    document.getElementById('rating-star-label').style.color = 'var(--red)';
  }
});

async function dismissRatingPrompt(){
  const button = document.getElementById('rating-maybe-later');
  if(button){button.disabled=true;button.innerHTML='<i class="fa-solid fa-spinner fa-spin"></i> Closing...';}
  try{
    await fetch(@json(route('ratings.dismiss', $pendingRating)),{
      method:'POST',
      headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':@json(csrf_token())},
      credentials:'same-origin'
    });
  }catch(error){}
  closeModal('ratingModal');
  document.body.classList.remove('rating-modal-open');
}
@endif

@if(isset($activeSession) && $activeSession)
(function () {
  let totalSeconds = {{ $activeSession->total_minutes * 60 }};
  let remainingSeconds = {{ $activeSession->remaining_seconds }};
  let running = true;
  let timerHandle = null;
  let warningPlayed = false;
  let alarmPlayed = false;

  function playBeep(frequency = 880, duration = 0.3) {
    try {
      const context = new (window.AudioContext || window.webkitAudioContext)();
      const oscillator = context.createOscillator();
      const gain = context.createGain();
      oscillator.connect(gain);
      gain.connect(context.destination);
      oscillator.type = 'sine';
      oscillator.frequency.value = frequency;
      gain.gain.setValueAtTime(0.4, context.currentTime);
      gain.gain.exponentialRampToValueAtTime(0.001, context.currentTime + duration);
      oscillator.start(context.currentTime);
      oscillator.stop(context.currentTime + duration);
    } catch (error) {}
  }

  function formatSeconds(seconds) {
    const minutes = Math.floor(seconds / 60);
    const remainder = seconds % 60;
    return String(minutes).padStart(2, '0') + ':' + String(remainder).padStart(2, '0');
  }

  function render() {
    remainingSeconds = Math.max(0, Number(remainingSeconds || 0));
    const countdown = document.getElementById('session-countdown');
    const progress = document.getElementById('session-progress');

    if (countdown) countdown.textContent = formatSeconds(remainingSeconds);
    if (progress) {
      const percentage = totalSeconds > 0 ? (remainingSeconds / totalSeconds * 100) : 0;
      progress.style.width = Math.max(0, Math.min(100, percentage)) + '%';
      if (remainingSeconds <= 60) progress.style.background = 'rgba(229,62,62,.9)';
      else if (remainingSeconds <= 300) progress.style.background = 'rgba(255,255,255,.6)';
      else progress.style.background = 'rgba(255,255,255,.8)';
    }

    if (countdown) {
      if (remainingSeconds <= 60) countdown.style.color = '#ffcccc';
      else if (remainingSeconds <= 300) countdown.style.color = '#fff3cd';
      else countdown.style.color = '#fff';
    }

    if (remainingSeconds <= 300 && !warningPlayed) {
      warningPlayed = true;
      playBeep(660, 0.25);
      setTimeout(() => playBeep(660, 0.25), 300);
    }

    if (remainingSeconds <= 0 && !alarmPlayed) {
      alarmPlayed = true;
      playBeep(880, 0.3);
      setTimeout(() => playBeep(660, 0.3), 350);
      setTimeout(() => playBeep(440, 0.6), 700);
      // Ask the server immediately to finalize the session instead of waiting
      // for the next scheduled real-time poll.
      setTimeout(() => window.ITCPollUserUpdates?.(), 250);
    }
  }

  function tick() {
    timerHandle = null;
    if (!running) return;
    render();
    if (remainingSeconds > 0) {
      remainingSeconds--;
      timerHandle = setTimeout(tick, 1000);
    }
  }

  window.ITCSessionTimer = {
    update(remaining, total) {
      const incomingRemaining = Math.max(0, Number(remaining || 0));
      const incomingTotal = Math.max(1, Number(total || totalSeconds));
      if (incomingRemaining > remainingSeconds + 2) {
        warningPlayed = incomingRemaining <= 300;
        alarmPlayed = false;
      }
      remainingSeconds = incomingRemaining;
      totalSeconds = incomingTotal;
      running = true;
      render();
      if (!timerHandle && remainingSeconds > 0) {
        timerHandle = setTimeout(tick, 1000);
      }
    },
    finish() {
      running = false;
      if (timerHandle) clearTimeout(timerHandle);
      timerHandle = null;
      remainingSeconds = 0;
      render();
    }
  };

  tick();
})();
@endif

(function tick(){
  const n=new Date(),h=n.getHours(),m=n.getMinutes(),s=n.getSeconds();
  const ap=h>=12?'PM':'AM',h12=h%12||12;
  const el=document.getElementById('clock');
  if(el)el.textContent=String(h12).padStart(2,'0')+':'+String(m).padStart(2,'0')+':'+String(s).padStart(2,'0')+' '+ap;
  setTimeout(tick,1000);
})();
</script>
@endpush
@endsection