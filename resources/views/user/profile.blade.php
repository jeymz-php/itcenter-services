@extends('layouts.app')
@section('title','My Profile | IT Center Services')
@section('body-class','dash-page')
@section('content')
@php $user = Auth::user(); @endphp

{{-- EDIT PROFILE MODAL --}}
<div class="modal-bg" id="editProfileModal">
  <div class="modal-box" style="max-width:540px">
    <div class="modal-hd">
      <h3><i class="fa-solid fa-pen" style="color:var(--g600);margin-right:6px"></i>Edit Profile</h3>
      <button class="modal-close" onclick="closeModal('editProfileModal')">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <form action="{{ route('profile.update') }}" method="POST">
      @csrf
      <div class="modal-body">
        <div class="abox info" style="margin-bottom:16px">
          <i class="fa-solid fa-circle-info"></i>
          <div>Your <strong>ID Number</strong> cannot be changed. Contact the IT Center if needed.</div>
        </div>
        <div class="g2">
          <div class="fg">
            <div class="flabel"><i class="fa-solid fa-user"></i> First Name</div>
            <input type="text" name="first_name" class="fc"
              value="{{ old('first_name', $user->first_name) }}" required>
          </div>
          <div class="fg">
            <div class="flabel"><i class="fa-solid fa-user"></i> Last Name</div>
            <input type="text" name="last_name" class="fc"
              value="{{ old('last_name', $user->last_name) }}" required>
          </div>
        </div>
        <div class="fg">
          <div class="flabel"><i class="fa-solid fa-envelope"></i> Email Address</div>
          <div class="iw">
            <i class="fa-solid fa-envelope ii"></i>
            <input type="email" name="email" class="fc"
              value="{{ old('email', $user->email) }}" required>
          </div>
        </div>
        <div class="fg">
          <div class="flabel"><i class="fa-solid fa-building-columns"></i> Campus</div>
          <div class="sw">
            <select name="campus" class="fs" required>
              @foreach(config('campuses') as $k => $v)
              <option value="{{ $k }}" {{ $user->campus === $k ? 'selected' : '' }}>{{ $v }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="fg">
          <div class="flabel"><i class="fa-solid fa-id-card"></i> ID Number</div>
          <input type="text" class="fc" value="{{ $user->id_number }}" disabled
            style="opacity:.55;cursor:not-allowed;background:var(--gray200)">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="modal-btn secondary" onclick="closeModal('editProfileModal')">
          Cancel
        </button>
        <button type="submit" class="modal-btn primary">
          <i class="fa-solid fa-floppy-disk"></i> Save Changes
        </button>
      </div>
    </form>
  </div>
</div>

{{-- TERMS MODAL --}}
<div class="modal-bg" id="termsModal">
  <div class="modal-box">
    <div class="modal-hd">
      <h3><i class="fa-solid fa-file-contract" style="color:var(--g600);margin-right:7px"></i>Terms & Conditions</h3>
      <button class="modal-close" onclick="closeModal('termsModal')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body">
      <h4>1. Account Responsibility</h4>
      <p>You are responsible for maintaining the confidentiality of your account credentials.</p>
      <h4>2. Use of Services</h4>
      <p>Services are exclusively for registered UCC students, faculty, and staff for academic purposes.</p>
      <h4>3. Account Deactivation</h4>
      <p>Accounts may be deactivated by the IT Center for policy violations. You may request reactivation.</p>
      <h4>4. Account Deletion</h4>
      <p>Deletion requests require admin approval. All associated data will be permanently removed.</p>
    </div>
    <div class="modal-footer">
      <button class="modal-btn secondary" onclick="closeModal('termsModal')">Close</button>
    </div>
  </div>
</div>

{{-- DATA PRIVACY MODAL --}}
<div class="modal-bg" id="privacyModal">
  <div class="modal-box">
    <div class="modal-hd">
      <h3><i class="fa-solid fa-shield-halved" style="color:var(--g600);margin-right:7px"></i>Data Privacy Notice</h3>
      <button class="modal-close" onclick="closeModal('privacyModal')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body">
      <p><strong>Pursuant to Republic Act No. 10173</strong> (Data Privacy Act of 2012), UCC IT Center is committed to protecting your personal information.</p>
      <h4>Data Collected</h4>
      <ul>
        <li>Full name, ID number, email address</li>
        <li>Campus affiliation and user type</li>
        <li>Profile photograph (optional)</li>
        <li>Service request history</li>
      </ul>
      <h4>Purpose</h4>
      <p>Your data is collected solely for managing IT Center service requests and verifying user identity.</p>
      <h4>Your Rights</h4>
      <ul>
        <li>Right to access, correct, and erase your data</li>
        <li>Right to lodge a complaint with the National Privacy Commission</li>
      </ul>
      <h4>Contact</h4>
      <p>Data Protection Officer: <strong>dpo@ucc.edu.ph</strong></p>
    </div>
    <div class="modal-footer">
      <button class="modal-btn secondary" onclick="closeModal('privacyModal')">Close</button>
    </div>
  </div>
</div>

<div class="dash-wrap">
  {{-- SIDEBAR --}}
  @include('user.partials.sidebar')

  <main class="main">
    {{-- TOPBAR --}}
    <div class="topbar">
      <div>
        <h1>My Profile</h1>
        <p>Manage your account details and settings</p>
      </div>
      <div class="topbar-right">
        <div class="clock">
          <i class="fa-solid fa-clock" style="color:var(--g600)"></i>
          <span id="clock">--:-- --</span>
        </div>
      </div>
    </div>

    <div class="content">

      {{-- ALERTS --}}
      @if(session('success'))
        <div class="abox ok" style="margin-bottom:16px">
          <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
      @endif
      @if($errors->any())
        <div class="abox err" style="margin-bottom:16px">
          <i class="fa-solid fa-triangle-exclamation"></i>
          <div>@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
        </div>
      @endif

      {{-- ACCOUNT INFO CARD --}}
      <div class="profile-card" style="margin-bottom:16px">
        <div class="profile-card-hd" style="justify-content:space-between;align-items:center">
          <span><i class="fa-solid fa-circle-user"></i> Account Information</span>
          <button onclick="openModal('editProfileModal')"
            style="background:var(--g100);color:var(--g700);border:1.5px solid var(--g300);border-radius:8px;padding:6px 14px;font-size:.75rem;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:6px;transition:all .2s"
            onmouseover="this.style.background='var(--g200)'"
            onmouseout="this.style.background='var(--g100)'">
            <i class="fa-solid fa-pen"></i> Edit Profile
          </button>
        </div>
        <div class="profile-card-body">
          <div style="display:flex;align-items:flex-start;gap:20px;flex-wrap:wrap">

            {{-- Avatar --}}
            <div style="text-align:center;flex-shrink:0">
              <div style="width:86px;height:86px;border-radius:50%;overflow:hidden;
                background:linear-gradient(135deg,var(--g500),var(--g700));
                display:flex;align-items:center;justify-content:center;
                color:#fff;font-weight:800;font-size:2rem;
                margin:0 auto 10px;border:3px solid var(--g200)">
                @if($user->profile_picture)
                  <img src="{{ Storage::url($user->profile_picture) }}"
                    style="width:100%;height:100%;object-fit:cover">
                @else
                  {{ strtoupper(substr($user->first_name,0,1)) }}
                @endif
              </div>
              <form action="{{ route('profile.photo') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <label style="font-size:.72rem;color:var(--g700);font-weight:600;cursor:pointer;
                  background:var(--g100);padding:5px 10px;border-radius:8px;display:inline-flex;
                  align-items:center;gap:4px;border:1.5px solid var(--g300)">
                  <i class="fa-solid fa-camera"></i> Change Photo
                  <input type="file" name="profile_picture" accept="image/*"
                    style="display:none" onchange="this.form.submit()">
                </label>
              </form>
            </div>

            {{-- Details Grid --}}
            <div style="flex:1;min-width:220px;display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:12px 20px">
              <div>
                <div style="font-size:.65rem;color:var(--gray400);font-weight:700;text-transform:uppercase;letter-spacing:.05em;margin-bottom:3px">Full Name</div>
                <div style="font-size:.88rem;font-weight:700;color:var(--gray800)">{{ $user->full_name }}</div>
              </div>
              <div>
                <div style="font-size:.65rem;color:var(--gray400);font-weight:700;text-transform:uppercase;letter-spacing:.05em;margin-bottom:3px">ID Number</div>
                <div style="font-size:.88rem;font-weight:700;color:var(--gray800);font-family:monospace">{{ $user->id_number }}</div>
              </div>
              <div>
                <div style="font-size:.65rem;color:var(--gray400);font-weight:700;text-transform:uppercase;letter-spacing:.05em;margin-bottom:3px">Email Address</div>
                <div style="font-size:.84rem;color:var(--gray800)">{{ $user->email }}</div>
              </div>
              <div>
                <div style="font-size:.65rem;color:var(--gray400);font-weight:700;text-transform:uppercase;letter-spacing:.05em;margin-bottom:3px">Campus</div>
                <div style="font-size:.84rem;color:var(--gray800)">{{ config('campuses.'.$user->campus) }}</div>
              </div>
              <div>
                <div style="font-size:.65rem;color:var(--gray400);font-weight:700;text-transform:uppercase;letter-spacing:.05em;margin-bottom:3px">User Type</div>
                <span class="tag {{ $user->user_type==='student'?'tag-student':'tag-faculty' }}">
                  {{ ucfirst(str_replace('_',' ',$user->user_type)) }}
                </span>
              </div>
              <div>
                <div style="font-size:.65rem;color:var(--gray400);font-weight:700;text-transform:uppercase;letter-spacing:.05em;margin-bottom:3px">Account Status</div>
                @php
                  $statusClasses = [
                    'pending'     => 'tag-pend',
                    'active'      => 'tag-active',
                    'deactivated' => 'tag-deact',
                    'archived'    => 'tag-arch',
                    'rejected'    => 'tag-rej',
                  ];
                @endphp
                <span class="tag {{ $statusClasses[$user->status] ?? '' }}">
                  {{ strtoupper($user->status) }}
                </span>
              </div>
              <div>
                <div style="font-size:.65rem;color:var(--gray400);font-weight:700;text-transform:uppercase;letter-spacing:.05em;margin-bottom:3px">Member Since</div>
                <div style="font-size:.84rem;color:var(--gray800)">{{ $user->created_at->format('F j, Y') }}</div>
              </div>
            </div>
          </div>

          {{-- Legal links --}}
          <div style="margin-top:16px;padding-top:14px;border-top:1px solid var(--gray100);display:flex;gap:12px;flex-wrap:wrap">
            <a href="#" onclick="openModal('termsModal');return false;"
              style="font-size:.74rem;color:var(--g700);font-weight:600;text-decoration:none;display:flex;align-items:center;gap:5px">
              <i class="fa-solid fa-file-contract"></i> Terms & Conditions
            </a>
            <a href="#" onclick="openModal('privacyModal');return false;"
              style="font-size:.74rem;color:var(--g700);font-weight:600;text-decoration:none;display:flex;align-items:center;gap:5px">
              <i class="fa-solid fa-shield-halved"></i> Data Privacy Policy
            </a>
          </div>
        </div>
      </div>

      {{-- TWO COLUMN GRID --}}
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">

        {{-- CHANGE PASSWORD --}}
        <div class="profile-card">
          <div class="profile-card-hd"><i class="fa-solid fa-lock"></i> Change Password</div>
          <div class="profile-card-body">
            <form action="{{ route('profile.password') }}" method="POST">
              @csrf
              <div class="fg">
                <div class="flabel"><i class="fa-solid fa-lock"></i> Current Password</div>
                <div class="iw">
                  <i class="fa-solid fa-lock ii"></i>
                  <input type="password" name="current_password" class="fc"
                    placeholder="Enter current password" required>
                  <button type="button" class="eye-btn" onclick="toggleEye('cp','ce1')">
                    <i class="fa-solid fa-eye" id="ce1"></i>
                  </button>
                </div>
              </div>
              <div class="fg">
                <div class="flabel"><i class="fa-solid fa-lock"></i> New Password</div>
                <div class="iw">
                  <i class="fa-solid fa-lock ii"></i>
                  <input type="password" name="password" id="np" class="fc"
                    placeholder="New password (8+, A-Z, 0-9, symbol)"
                    oninput="checkStrength(this.value)" required>
                  <button type="button" class="eye-btn" onclick="toggleEye('np','ne1')">
                    <i class="fa-solid fa-eye" id="ne1"></i>
                  </button>
                </div>
                <div class="str-bar">
                  <div class="str-seg" id="s1"></div>
                  <div class="str-seg" id="s2"></div>
                  <div class="str-seg" id="s3"></div>
                  <div class="str-seg" id="s4"></div>
                </div>
                <div class="str-txt" id="str-lbl">Enter a password</div>
              </div>
              <div class="fg">
                <div class="flabel"><i class="fa-solid fa-lock"></i> Confirm New Password</div>
                <div class="iw">
                  <i class="fa-solid fa-lock ii"></i>
                  <input type="password" name="password_confirmation" id="cp2" class="fc"
                    placeholder="Re-enter new password" required>
                  <button type="button" class="eye-btn" onclick="toggleEye('cp2','ce2')">
                    <i class="fa-solid fa-eye" id="ce2"></i>
                  </button>
                </div>
              </div>
              <button type="submit" class="btn">
                <i class="fa-solid fa-key"></i> Change Password
              </button>
            </form>
          </div>
        </div>

        {{-- REQUEST HISTORY --}}
        <div class="profile-card">
          <div class="profile-card-hd">
            <i class="fa-solid fa-clock-rotate-left"></i> Account Request History
          </div>
          <div class="profile-card-body" style="padding:0;max-height:300px;overflow-y:auto">
            @forelse($requests as $req)
            <div style="padding:12px 18px;border-bottom:1px solid var(--gray100);display:flex;align-items:flex-start;gap:10px">
              <div style="width:32px;height:32px;border-radius:8px;flex-shrink:0;
                background:{{ $req->type==='delete'?'var(--red-bg)':($req->type==='deactivate'?'#f3e5f5':'var(--g100)') }};
                display:flex;align-items:center;justify-content:center;
                color:{{ $req->type==='delete'?'var(--red)':($req->type==='deactivate'?'#7b1fa2':'var(--g600)') }};
                font-size:.82rem">
                <i class="fa-solid fa-{{ $req->type==='delete'?'trash':($req->type==='deactivate'?'user-slash':'user-check') }}"></i>
              </div>
              <div style="flex:1;min-width:0">
                <div style="font-size:.78rem;font-weight:700;color:var(--gray800);text-transform:capitalize">
                  {{ str_replace('_',' ',$req->type) }} Request
                </div>
                <div style="font-size:.68rem;color:var(--gray400);margin-top:1px">
                  {{ $req->created_at->format('M d, Y g:i A') }}
                </div>
                <div style="font-size:.72rem;color:var(--gray600);margin-top:3px;line-height:1.4">
                  {{ Str::limit($req->reason, 70) }}
                </div>
                @if($req->admin_note)
                <div style="font-size:.68rem;color:var(--orange);margin-top:2px">
                  <i class="fa-solid fa-note-sticky"></i> {{ $req->admin_note }}
                </div>
                @endif
              </div>
              <span class="tag {{ $req->status==='pending'?'tag-pend':($req->status==='approved'?'tag-active':'tag-rej') }}"
                style="font-size:.62rem;flex-shrink:0">
                {{ strtoupper($req->status) }}
              </span>
            </div>
            @empty
            <div style="padding:24px;text-align:center;color:var(--gray400);font-size:.78rem">
              <i class="fa-solid fa-inbox" style="display:block;font-size:1.3rem;margin-bottom:6px"></i>
              No account requests yet.
            </div>
            @endforelse
          </div>
        </div>

        {{-- DEACTIVATE (active users only) --}}
        @if($user->status === 'active')
        <div class="profile-card">
          <div class="profile-card-hd" style="color:#7b1fa2">
            <i class="fa-solid fa-user-slash"></i> Request Account Deactivation
          </div>
          <div class="profile-card-body">
            @if($user->pendingRequest('deactivate'))
              <div class="abox warn">
                <i class="fa-solid fa-hourglass-half"></i>
                <div>You have a <strong>pending deactivation request</strong> awaiting admin review.</div>
              </div>
            @else
              <p style="font-size:.78rem;color:var(--gray600);margin-bottom:14px;line-height:1.65">
                Requesting deactivation will temporarily disable your account. You can request reactivation anytime.
              </p>
              <form action="{{ route('profile.deactivate') }}" method="POST">
                @csrf
                <div class="fg">
                  <div class="flabel">Reason for Deactivation</div>
                  <textarea name="reason" class="fc" rows="3"
                    placeholder="Please state your reason..."
                    required style="resize:vertical"></textarea>
                </div>
                <button type="submit" class="btn"
                  style="background:linear-gradient(135deg,#7b1fa2,#ab47bc)"
                  onclick="return confirm('Submit deactivation request?')">
                  <i class="fa-solid fa-user-slash"></i> Submit Deactivation Request
                </button>
              </form>
            @endif
          </div>
        </div>
        @endif

        {{-- REACTIVATE (deactivated users only) --}}
        @if($user->status === 'deactivated')
        <div class="profile-card">
          <div class="profile-card-hd" style="color:var(--g600)">
            <i class="fa-solid fa-user-check"></i> Request Account Reactivation
          </div>
          <div class="profile-card-body">
            @if($user->pendingRequest('reactivate'))
              <div class="abox info">
                <i class="fa-solid fa-hourglass-half"></i>
                <div>You have a <strong>pending reactivation request</strong> awaiting admin review.</div>
              </div>
            @else
              <form action="{{ route('profile.reactivate') }}" method="POST">
                @csrf
                <div class="fg">
                  <div class="flabel">Reason for Reactivation</div>
                  <textarea name="reason" class="fc" rows="3"
                    placeholder="Please state your reason..."
                    required style="resize:vertical"></textarea>
                </div>
                <button type="submit" class="btn">
                  <i class="fa-solid fa-user-check"></i> Submit Reactivation Request
                </button>
              </form>
            @endif
          </div>
        </div>
        @endif

        {{-- DELETE ACCOUNT --}}
        <div class="profile-card" style="{{ !in_array($user->status,['active','deactivated'])?'grid-column:1/-1':'' }}">
          <div class="profile-card-hd" style="color:var(--red)">
            <i class="fa-solid fa-trash"></i> Delete Account
          </div>
          <div class="profile-card-body">
            @if($user->pendingRequest('delete'))
              <div class="abox warn">
                <i class="fa-solid fa-hourglass-half"></i>
                <div>You have a <strong>pending account deletion request</strong> awaiting admin approval.</div>
              </div>
            @else
              <div class="abox err" style="margin-bottom:14px">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <div>
                  <strong>Warning:</strong> This action is <strong>permanent</strong>.
                  Your account and all associated data will be deleted upon admin approval.
                </div>
              </div>
              <form action="{{ route('profile.delete') }}" method="POST">
                @csrf
                <div class="fg">
                  <div class="flabel">Reason for Deletion</div>
                  <textarea name="reason" class="fc" rows="3"
                    placeholder="Please state your reason..."
                    required style="resize:vertical"></textarea>
                </div>
                <button type="submit" class="btn"
                  style="background:linear-gradient(135deg,#c62828,var(--red))"
                  onclick="return confirm('Are you sure? This cannot be undone once approved.')">
                  <i class="fa-solid fa-trash"></i> Submit Deletion Request
                </button>
              </form>
            @endif
          </div>
        </div>

      </div>{{-- end grid --}}
    </div>{{-- end content --}}
  </main>
</div>

@push('scripts')
<script>
// Modal controls
function openModal(id){ document.getElementById(id).classList.add('open') }
function closeModal(id){ document.getElementById(id).classList.remove('open') }
document.querySelectorAll('.modal-bg').forEach(m =>
  m.addEventListener('click', e => { if(e.target === m) m.classList.remove('open') })
);

// Auto-open edit modal if there are validation errors from profile update
@if($errors->hasAny(['first_name','last_name','email','campus']))
  document.addEventListener('DOMContentLoaded', () => openModal('editProfileModal'));
@endif

// Password show/hide
function toggleEye(fid, eid){
  const f = document.getElementById(fid), i = document.getElementById(eid);
  f.type = f.type === 'password' ? 'text' : 'password';
  i.className = f.type === 'text' ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
}

// Password strength meter
function checkStrength(v){
  let s = 0;
  if(v.length >= 8) s++;
  if(/[A-Z]/.test(v)) s++;
  if(/[0-9]/.test(v)) s++;
  if(/[@$!%*#?&]/.test(v)) s++;
  const segs = document.querySelectorAll('.str-seg');
  const cls  = ['','s1','s2','s3','s4'];
  const lbls = ['','Weak','Fair','Good','Strong'];
  segs.forEach((seg,i) => { seg.className='str-seg'; if(i<s) seg.classList.add(cls[s]); });
  const lbl = document.getElementById('str-lbl');
  if(lbl){
    lbl.textContent = v.length ? lbls[s] : 'Enter a password';
    lbl.style.color = s<=1?'var(--red)':s===2?'var(--orange)':s===3?'var(--g400)':'var(--g600)';
  }
}

// Clock
(function tick(){
  const n=new Date(), h=n.getHours(), m=n.getMinutes(), s=n.getSeconds();
  const ap=h>=12?'PM':'AM', h12=h%12||12;
  const el=document.getElementById('clock');
  if(el) el.textContent=String(h12).padStart(2,'0')+':'+String(m).padStart(2,'0')+':'+String(s).padStart(2,'0')+' '+ap;
  setTimeout(tick,1000);
})();
</script>
@endpush
@endsection