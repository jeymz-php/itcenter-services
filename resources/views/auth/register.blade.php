@extends('layouts.app')
@section('title','Register | IT Center Services System')
@section('content')
@section('body-class', 'auth-page')
@section('auth-bg', true)

<!-- TERMS MODAL -->
<div class="modal-bg" id="termsModal">
  <div class="modal-box">
    <div class="modal-hd">
      <h3><i class="fa-solid fa-file-contract" style="color:var(--g600);margin-right:7px"></i>Terms &amp; Conditions</h3>
      <button class="modal-close" onclick="closeModal('termsModal')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body">
      <h4>1. Acceptance of Terms</h4>
      <p>By registering and using the IT Center Services System of the University of Caloocan City (UCC), you agree to abide by these terms and conditions.</p>
      <h4>2. Use of Services</h4>
      <p>The system is exclusively for registered UCC students, faculty, and staff. You may only use the services for academic and institutional purposes.</p>
      <h4>3. Account Responsibility</h4>
      <p>You are responsible for maintaining the confidentiality of your account credentials. Any activity performed under your account is your responsibility.</p>
      <h4>4. Prohibited Activities</h4>
      <ul>
        <li>Using another person's account</li>
        <li>Uploading malicious files for printing</li>
        <li>Misrepresenting your identity or affiliation</li>
        <li>Attempting to access restricted areas of the system</li>
      </ul>
      <h4>5. Service Availability</h4>
      <p>The IT Center reserves the right to suspend, modify, or discontinue services at any time without prior notice. Scheduled maintenance will be communicated in advance when possible.</p>
      <h4>6. Violations</h4>
      <p>Violations of these terms may result in account suspension or permanent ban. Serious violations may be referred to the Office of Student Affairs or higher authorities.</p>
      <h4>7. Amendments</h4>
      <p>UCC IT Center reserves the right to update these terms at any time. Continued use of the system constitutes acceptance of any changes.</p>
    </div>
  </div>
</div>

<!-- DATA PRIVACY MODAL -->
<div class="modal-bg" id="privacyModal">
  <div class="modal-box">
    <div class="modal-hd">
      <h3><i class="fa-solid fa-shield-halved" style="color:var(--g600);margin-right:7px"></i>Data Privacy Notice</h3>
      <button class="modal-close" onclick="closeModal('privacyModal')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body">
      <p><strong>Pursuant to Republic Act No. 10173</strong>, otherwise known as the <strong>Data Privacy Act of 2012</strong>, the University of Caloocan City IT Center is committed to protecting your personal information.</p>
      <h4>Personal Data Collected</h4>
      <ul>
        <li>Full name and ID number</li>
        <li>Email address</li>
        <li>Profile photograph (optional)</li>
        <li>Campus affiliation and user type</li>
        <li>Service request history</li>
      </ul>
      <h4>Purpose of Data Collection</h4>
      <p>Your data is collected solely for the purpose of managing IT Center service requests, verifying user identity, and improving service delivery within UCC.</p>
      <h4>Data Sharing</h4>
      <p>Your personal information will not be shared with third parties outside of UCC without your consent, except as required by law.</p>
      <h4>Data Retention</h4>
      <p>Personal data will be retained for the duration of your enrollment or employment at UCC and up to five (5) years thereafter, in compliance with RA 10173 and CHED regulations.</p>
      <h4>Your Rights Under RA 10173</h4>
      <ul>
        <li>Right to be informed about data collection</li>
        <li>Right to access your personal data</li>
        <li>Right to correction of inaccurate data</li>
        <li>Right to erasure or blocking of unlawfully processed data</li>
        <li>Right to lodge a complaint with the National Privacy Commission</li>
      </ul>
      <h4>Contact</h4>
      <p>For data privacy concerns, contact the UCC Data Protection Officer at: <strong>dpo@ucc.edu.ph</strong></p>
    </div>
  </div>
</div>

<div class="auth-wrap" style="max-width:1050px">
  <!-- LEFT -->
  <div class="panel-left">
    <div class="logo-row">
      <img src="{{ asset('images/UCC_Logo.png') }}" alt="UCC">
      <div class="logo-text">
        <div class="sys-name">IT Center Services System</div>
        <div class="sys-sub">Join our IT Center Services System</div>
      </div>
    </div>
    <div class="left-title">Create<br>Your Account</div>
    <div class="left-desc">Register to access printing, photocopy, and computer lab services at any UCC campus.</div>
    <div class="feat-list">
      <div class="feat-item">
        <div class="feat-icon"><i class="fa-solid fa-shield-halved"></i></div>
        <div><div class="feat-title">Secure Registration</div><div class="feat-sub">Protected with encryption under RA 10173</div></div>
      </div>
      <div class="feat-item">
        <div class="feat-icon"><i class="fa-solid fa-bolt"></i></div>
        <div><div class="feat-title">Quick Setup</div><div class="feat-sub">Get started in less than 2 minutes</div></div>
      </div>
      <div class="feat-item">
        <div class="feat-icon"><i class="fa-solid fa-headset"></i></div>
        <div><div class="feat-title">24/7 Support</div><div class="feat-sub">Help available whenever you need it</div></div>
      </div>
    </div>
    <div class="stats-row">
      <div class="stat"><div class="sv">24/7</div><div class="sl">Online Access</div></div>
      <div class="stat"><div class="sv">Fast</div><div class="sl">Processing</div></div>
      <div class="stat"><div class="sv">Secure</div><div class="sl">System</div></div>
    </div>
  </div>

  <!-- RIGHT -->
  <div class="panel-right">
    <div class="form-hd"><h2>Create Your Account</h2><p>Fill in your details to get started</p></div>

    @if($errors->any())
      <div class="abox err">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <div>@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
      </div>
    @endif

    <form action="{{ route('register.post') }}" method="POST" enctype="multipart/form-data">
      @csrf

      <!-- Profile Picture -->
      <div class="fg">
        <div class="flabel"><i class="fa-solid fa-camera"></i> Profile Picture <span style="color:var(--gray400);font-weight:400">(Optional – Recommended)</span></div>
        <div class="pic-upload" onclick="document.getElementById('pic-input').click()">
          <div class="pic-preview" id="pic-preview"><i class="fa-solid fa-user"></i></div>
          <div class="pic-txt">
            <div class="pt1">Click to upload photo</div>
            <div class="pt2">JPG, PNG or WEBP · Max 2MB · Helps verify your identity</div>
          </div>
        </div>
        <input type="file" id="pic-input" name="profile_picture" accept="image/*" style="display:none" onchange="previewPic(this)">
      </div>

      <!-- ID Number -->
      <div class="fg">
        <div class="flabel"><i class="fa-solid fa-id-card"></i> ID Number</div>
        <input type="text" name="id_number" class="fc" placeholder="Enter 8-digit ID number" maxlength="8" value="{{ old('id_number') }}" required>
      </div>

      <!-- Name -->
      <div class="g2">
        <div class="fg">
          <div class="flabel"><i class="fa-solid fa-user"></i> First Name</div>
          <input type="text" name="first_name" class="fc" placeholder="First name" value="{{ old('first_name') }}" required>
        </div>
        <div class="fg">
          <div class="flabel"><i class="fa-solid fa-user"></i> Last Name</div>
          <input type="text" name="last_name" class="fc" placeholder="Last name" value="{{ old('last_name') }}" required>
        </div>
      </div>

      <!-- Email -->
      <div class="fg">
        <div class="flabel"><i class="fa-solid fa-envelope"></i> Email Address</div>
        <div class="iw">
          <i class="fa-solid fa-envelope ii"></i>
          <input type="email" name="email" class="fc" placeholder="Enter your email address" value="{{ old('email') }}" required>
        </div>
      </div>

      <!-- Campus & User Type -->
      <div class="g2">
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
              <option value="" disabled selected>Select type</option>
              <option value="student"       {{ old('user_type')=='student'?'selected':'' }}>Student</option>
              <option value="faculty_staff" {{ old('user_type')=='faculty_staff'?'selected':'' }}>Faculty / Staff</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Password -->
      <div class="fg">
        <div class="flabel"><i class="fa-solid fa-lock"></i> Password</div>
        <div class="iw">
          <i class="fa-solid fa-lock ii"></i>
          <input type="password" name="password" id="rpass" class="fc" placeholder="Password (8+, Uppercase, Number, Symbol)" oninput="checkStrength(this.value)" required>
          <button type="button" class="eye-btn" onclick="toggleEye('rpass','re1')"><i class="fa-solid fa-eye" id="re1"></i></button>
        </div>
        <div class="str-bar">
          <div class="str-seg" id="s1"></div>
          <div class="str-seg" id="s2"></div>
          <div class="str-seg" id="s3"></div>
          <div class="str-seg" id="s4"></div>
        </div>
        <div class="str-txt" id="str-lbl">Enter a password</div>
      </div>

      <!-- Confirm Password -->
      <div class="fg">
        <div class="flabel"><i class="fa-solid fa-lock"></i> Confirm Password</div>
        <div class="iw">
          <i class="fa-solid fa-lock ii"></i>
          <input type="password" name="password_confirmation" id="rpass2" class="fc" placeholder="Re-enter your password" required>
          <button type="button" class="eye-btn" onclick="toggleEye('rpass2','re2')"><i class="fa-solid fa-eye" id="re2"></i></button>
        </div>
      </div>

      <!-- Terms -->
      <label class="cb-row">
        <input type="checkbox" name="terms" value="1" required {{ old('terms')?'checked':'' }}>
        I agree to the
        <a href="#" onclick="openModal('termsModal');return false;">Terms &amp; Conditions</a>
        and
        <a href="#" onclick="openModal('privacyModal');return false;">Data Privacy Policy</a>
      </label>

      <button type="submit" class="btn" style="margin-top:12px">
        <i class="fa-solid fa-user-plus"></i> Create Account
      </button>
    </form>

    <div class="divider">or</div>
    <div class="form-foot">Already have an account? <a href="{{ route('login') }}">Sign in here</a></div>
  </div>
</div>
@endsection
@push('scripts')
<script>
function toggleEye(f,e){
  const fi=document.getElementById(f),ic=document.getElementById(e);
  fi.type=fi.type==='password'?'text':'password';
  ic.className=fi.type==='text'?'fa-solid fa-eye-slash':'fa-solid fa-eye';
}
function openModal(id){document.getElementById(id).classList.add('open')}
function closeModal(id){document.getElementById(id).classList.remove('open')}
document.querySelectorAll('.modal-bg').forEach(m=>{
  m.addEventListener('click',e=>{if(e.target===m)m.classList.remove('open')});
});

function previewPic(input){
  if(!input.files||!input.files[0])return;
  const r=new FileReader();
  r.onload=e=>{
    const p=document.getElementById('pic-preview');
    p.innerHTML=`<img src="${e.target.result}" alt="Preview">`;
  };
  r.readAsDataURL(input.files[0]);
}

function checkStrength(v){
  let s=0;
  if(v.length>=8)s++;
  if(/[A-Z]/.test(v))s++;
  if(/[0-9]/.test(v))s++;
  if(/[@$!%*#?&]/.test(v))s++;
  const segs=document.querySelectorAll('.str-seg');
  const lbls=['','Weak','Fair','Good','Strong'];
  const cls=['','s1','s2','s3','s4'];
  segs.forEach((seg,i)=>{
    seg.className='str-seg';
    if(i<s)seg.classList.add(cls[s]);
  });
  document.getElementById('str-lbl').textContent=v.length?lbls[s]:'Enter a password';
  document.getElementById('str-lbl').style.color=
    s<=1?'#e53e3e':s===2?'#f5a623':s===3?'var(--g400)':'var(--g600)';
}
</script>
@endpush