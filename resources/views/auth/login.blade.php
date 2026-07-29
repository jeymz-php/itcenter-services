@extends('layouts.app')
@section('title','Login | IT Center Services System')
@section('content')
@section('body-class', 'auth-page')
@section('auth-bg', true)
<div class="auth-wrap">
  <!-- LEFT -->
  <div class="panel-left">
    <div class="logo-row">
      <img src="{{ asset('images/UCC_Logo.png') }}" alt="UCC" id="logo-trigger">
      <div class="logo-text">
        <div class="sys-name">IT Center Services System</div>
        <div class="sys-sub">Computer Lab &amp; Printing Services</div>
      </div>
    </div>
    <div class="left-title">Welcome to<br>UCC IT Center</div>
    <div class="left-desc">Access printing, photocopy, and computer lab services — all in one place for UCC students and staff.</div>
    <div class="feat-list">
      <div class="feat-item">
        <div class="feat-icon"><i class="fa-solid fa-print"></i></div>
        <div><div class="feat-title">Printing Services</div><div class="feat-sub">High-quality document &amp; photo printing</div></div>
      </div>
      <div class="feat-item">
        <div class="feat-icon"><i class="fa-solid fa-copy"></i></div>
        <div><div class="feat-title">Photocopy</div><div class="feat-sub">Fast and reliable copying services</div></div>
      </div>
      <div class="feat-item">
        <div class="feat-icon"><i class="fa-solid fa-desktop"></i></div>
        <div><div class="feat-title">Computer Lab</div><div class="feat-sub">Research &amp; computer reservation</div></div>
      </div>
    </div>

    @if($publicRatings->count())
    <div class="testimonial-carousel" style="margin-top:18px">
      <div style="font-size:.66rem;font-weight:700;color:rgba(255,255,255,.7);text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px">
        <i class="fa-solid fa-star" style="color:#ffc94d"></i> What Our Users Say
      </div>
      <div style="position:relative;min-height:112px">
        @foreach($publicRatings as $i => $rating)
        <div class="testimonial-card" data-idx="{{ $i }}" style="display:{{ $i===0?'block':'none' }};background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.15);border-radius:12px;padding:12px 14px">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;flex-wrap:wrap;gap:6px">
            <div style="display:flex;align-items:center;gap:6px">
              <span style="font-size:.78rem;font-weight:800;color:#fff">
                {{ $rating->display_first_name }}{{ $rating->display_last_name ? ' '.$rating->display_last_name : '' }}
              </span>
              <span style="font-size:.6rem;font-weight:700;padding:1px 7px;border-radius:8px;background:rgba(255,255,255,.15);color:rgba(255,255,255,.8)">
                {{ $rating->is_anonymous ? 'Anonymous' : 'Public' }}
              </span>
            </div>
            <div style="color:#ffc94d;font-size:.7rem">
              @for($s=1;$s<=5;$s++)<i class="fa-{{ $s<=$rating->stars?'solid':'regular' }} fa-star"></i>@endfor
            </div>
          </div>
          <div style="font-size:.66rem;color:rgba(255,255,255,.6);margin-bottom:6px">
            ID: {{ $rating->display_id_number }} &middot; {{ $rating->display_campus }}
          </div>
          <div style="font-size:.76rem;color:rgba(255,255,255,.9);line-height:1.5;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden">
            "{{ $rating->comment }}"
          </div>
        </div>
        @endforeach
      </div>
      @if($publicRatings->count() > 1)
      <div style="display:flex;gap:5px;justify-content:center;margin-top:10px">
        @foreach($publicRatings as $i => $rating)
        <span class="testimonial-dot" data-idx="{{ $i }}" style="width:6px;height:6px;border-radius:50%;background:rgba(255,255,255,{{ $i===0?'.9':'.3' }});cursor:pointer;transition:background .2s"></span>
        @endforeach
      </div>
      @endif
    </div>
    @endif

    <div class="stats-row">
      <div class="stat"><div class="sv">24/7</div><div class="sl">Online Access</div></div>
      <div class="stat"><div class="sv">Fast</div><div class="sl">Processing</div></div>
      <div class="stat"><div class="sv">Secure</div><div class="sl">System</div></div>
    </div>
  </div>

  <!-- RIGHT -->
  <div class="panel-right">
    <div class="form-hd"><h2>Welcome Back!</h2><p>Sign in to your account</p></div>

    @if(session('success'))
      <div class="abox ok"><i class="fa-solid fa-circle-check"></i><span>{{ session('success') }}</span></div>
    @endif
    @if($errors->has('login'))
      <div class="abox err"><i class="fa-solid fa-triangle-exclamation"></i><span>{{ $errors->first('login') }}</span></div>
    @endif

    <form action="{{ route('login.post') }}" method="POST">
      @csrf
      <div class="fg">
        <div class="flabel"><i class="fa-solid fa-building-columns"></i> Campus</div>
        <div class="sw">
          <select name="campus" class="fs" required>
            <option value="" disabled selected>Select Campus</option>
            @foreach(config('campuses') as $key => $label)
              <option value="{{ $key }}" {{ old('campus')==$key?'selected':'' }}>{{ $label }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <div class="fg">
        <div class="flabel"><i class="fa-solid fa-user-tag"></i> I am a</div>
        <div class="sw">
          <select name="user_type" class="fs" required>
            <option value="" disabled selected>Select user type</option>
            <option value="student"       {{ old('user_type')=='student'?'selected':'' }}>Student</option>
            <option value="faculty_staff" {{ old('user_type')=='faculty_staff'?'selected':'' }}>Faculty / Staff</option>
          </select>
        </div>
      </div>

      <div class="fg">
        <div class="flabel"><i class="fa-solid fa-id-card"></i> ID Number</div>
        <div class="iw">
          <i class="fa-solid fa-user ii"></i>
          <input type="text" name="id_number" class="fc" placeholder="Enter your ID number" value="{{ old('id_number') }}" required>
        </div>
      </div>

      <div class="fg">
        <div class="flabel"><i class="fa-solid fa-lock"></i> Password</div>
        <div class="iw">
          <i class="fa-solid fa-lock ii"></i>
          <input type="password" name="password" id="lpass" class="fc" placeholder="Enter your password" required>
          <button type="button" class="eye-btn" onclick="toggleEye('lpass','le1')"><i class="fa-solid fa-eye" id="le1"></i></button>
        </div>
        <div style="text-align:right;margin-top:6px">
          <a href="{{ route('password.request') }}" style="font-size:.74rem;color:var(--g700);font-weight:700;text-decoration:none">Forgot Password?</a>
        </div>
      </div>

      <button type="submit" class="btn"><i class="fa-solid fa-right-to-bracket"></i> Sign In</button>
    </form>

    <div class="divider">or</div>
    <div class="form-foot">Don't have an account? <a href="{{ route('register') }}">Sign up now</a></div>

    <div class="abox info" style="margin-top:16px">
      <i class="fa-solid fa-circle-question"></i>
      <div><strong>Need Help?</strong><br>Contact IT Center Services Desk for account assistance.</div>
    </div>

    <div style="display:flex;gap:8px;margin-top:12px">
      <button type="button" onclick="openModal('userGuideModal')" class="btn"
        style="background:var(--white);color:var(--g700);border:1.5px solid var(--gray200);box-shadow:none;flex:1;font-size:.74rem;padding:9px">
        <img src="{{ asset('images/icons/user-guide_icon.png') }}" alt="" style="width:15px;height:15px;object-fit:contain">
        User Manual &amp; Guide
      </button>
      <button type="button" onclick="openModal('developerModal')" class="btn"
        style="background:var(--white);color:var(--g700);border:1.5px solid var(--gray200);box-shadow:none;flex:1;font-size:.74rem;padding:9px">
        <img src="{{ asset('images/icons/developer_icon.png') }}" alt="" style="width:15px;height:15px;object-fit:contain">
        Developer
      </button>
    </div>

    <div class="cpr">
      &copy; {{ date('Y') }} IT Services System. All rights reserved. &nbsp;·&nbsp; v{{ \App\Models\Setting::get('system_version', '1.0.0') }}<br>
      <i class="fa-solid fa-phone" style="font-size:.6rem"></i> (02) 1234-5678 &nbsp;
      <i class="fa-solid fa-envelope" style="font-size:.6rem"></i> support@itservices.ph
    </div>
  </div>
</div>

{{-- USER MANUAL & GUIDE MODAL --}}
<div class="modal-bg" id="userGuideModal">
  <div class="modal-box" style="max-width:1100px;width:96vw;height:92vh;max-height:92vh">
    <div class="modal-hd">
      <h3>
        <img src="{{ asset('images/icons/user-guide_icon.png') }}" alt="" style="width:18px;height:18px;vertical-align:middle;margin-right:7px;object-fit:contain">
        User Manual &amp; Guide
      </h3>
      <button class="modal-close" onclick="closeModal('userGuideModal')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body" style="padding:0;flex:1;display:flex;flex-direction:column;min-height:0">
      <iframe src="{{ asset('documents/user-manual.pdf') }}#view=FitH" style="width:100%;height:100%;border:none;flex:1"></iframe>
    </div>
    <div class="modal-footer">
      <a href="{{ asset('documents/user-manual.pdf') }}" target="_blank" class="modal-btn secondary"
         style="text-decoration:none;display:inline-flex;align-items:center;gap:6px">
        <i class="fa-solid fa-up-right-from-square"></i> Open in New Tab
      </a>
      <button type="button" class="modal-btn primary" onclick="closeModal('userGuideModal')">Close</button>
    </div>
  </div>
</div>

{{-- DEVELOPER MODAL --}}
<div class="modal-bg" id="developerModal">
  <div class="modal-box" style="max-width:420px">
    <div class="modal-hd">
      <h3>
        <img src="{{ asset('images/icons/developer_icon.png') }}" alt="" style="width:18px;height:18px;vertical-align:middle;margin-right:7px;object-fit:contain">
        Developer
      </h3>
      <button class="modal-close" onclick="closeModal('developerModal')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body" style="text-align:center;padding:28px 24px">
      <img src="{{ asset('images/greg.jpg') }}" alt="James Ryan Gregorio"
        style="width:96px;height:96px;border-radius:50%;object-fit:cover;border:3px solid var(--g200);margin-bottom:14px">
      <div style="font-size:1.05rem;font-weight:800;color:var(--gray800)">James Ryan Gregorio</div>
      <div style="font-size:.8rem;color:var(--g600);font-weight:700;margin-top:2px">Full Stack Developer</div>
      <p style="font-size:.78rem;color:var(--gray600);margin-top:14px;line-height:1.6">
        Developed the UCC IT Center Services System — a platform for managing printing, photocopy, and computer lab requests for the University of Caloocan City.
      </p>
    </div>
    <div class="modal-footer" style="justify-content:center">
      <button type="button" class="modal-btn secondary" onclick="closeModal('developerModal')">Close</button>
    </div>
  </div>
</div>
@endsection
@push('scripts')
<script>
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.modal-bg').forEach(m=>m.addEventListener('click',e=>{if(e.target===m)m.classList.remove('open')}));

function toggleEye(fid,eid){
  const f=document.getElementById(fid),i=document.getElementById(eid);
  f.type=f.type==='password'?'text':'password';
  i.className=f.type==='text'?'fa-solid fa-eye-slash':'fa-solid fa-eye';
}
let _c=0,_t;
document.getElementById('logo-trigger').addEventListener('click',()=>{
  _c++;clearTimeout(_t);
  if(_c>=5){window.location='{{ route("admin.login") }}';_c=0;}
  else _t=setTimeout(()=>_c=0,2000);
});

// Testimonial carousel — auto-advances every 5s, dots allow manual jump.
const testimonialCards = document.querySelectorAll('.testimonial-card');
const testimonialDots  = document.querySelectorAll('.testimonial-dot');
let testimonialIdx = 0;
let testimonialTimer;

function showTestimonial(idx) {
  testimonialCards.forEach(c => c.style.display = c.dataset.idx == idx ? 'block' : 'none');
  testimonialDots.forEach(d => d.style.background = d.dataset.idx == idx ? 'rgba(255,255,255,.9)' : 'rgba(255,255,255,.3)');
  testimonialIdx = idx;
}

function startTestimonialAutoplay() {
  clearInterval(testimonialTimer);
  if (testimonialCards.length <= 1) return;
  testimonialTimer = setInterval(() => {
    showTestimonial((testimonialIdx + 1) % testimonialCards.length);
  }, 5000);
}

testimonialDots.forEach(dot => {
  dot.addEventListener('click', () => {
    showTestimonial(parseInt(dot.dataset.idx, 10));
    startTestimonialAutoplay();
  });
});

if (testimonialCards.length) startTestimonialAutoplay();
</script>
@endpush