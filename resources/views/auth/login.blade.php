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
      </div>

      <button type="submit" class="btn"><i class="fa-solid fa-right-to-bracket"></i> Sign In</button>
    </form>

    <div class="divider">or</div>
    <div class="form-foot">Don't have an account? <a href="{{ route('register') }}">Sign up now</a></div>

    <div class="abox info" style="margin-top:16px">
      <i class="fa-solid fa-circle-question"></i>
      <div><strong>Need Help?</strong><br>Contact IT Center Services Desk for account assistance.</div>
    </div>

    <div class="cpr">
      &copy; {{ date('Y') }} IT Services System. All rights reserved.<br>
      <i class="fa-solid fa-phone" style="font-size:.6rem"></i> (02) 1234-5678 &nbsp;
      <i class="fa-solid fa-envelope" style="font-size:.6rem"></i> support@itservices.ph
    </div>
  </div>
</div>
@endsection
@push('scripts')
<script>
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
</script>
@endpush