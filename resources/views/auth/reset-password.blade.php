@extends('layouts.app')
@section('title','Reset Password | IT Center Services System')
@section('content')
@section('body-class', 'auth-page')
@section('auth-bg', true)
<div class="auth-wrap">
  <div class="panel-left">
    <div class="logo-row">
      <img src="{{ asset('images/UCC_Logo.png') }}" alt="UCC">
      <div class="logo-text">
        <div class="sys-name">IT Center Services System</div>
        <div class="sys-sub">Account Recovery</div>
      </div>
    </div>
    <div class="left-title">Choose a New<br>Password</div>
    <div class="left-desc">Pick something secure that you haven't used before on this account.</div>
  </div>

  <div class="panel-right">
    <div class="form-hd"><h2>Reset Password</h2><p>Enter your new password below</p></div>

    @if($errors->any())
      <div class="abox err"><i class="fa-solid fa-triangle-exclamation"></i><span>{{ $errors->first() }}</span></div>
    @endif

    <form action="{{ route('password.update') }}" method="POST">
      @csrf
      <input type="hidden" name="token" value="{{ $token }}">
      <input type="hidden" name="email" value="{{ $email }}">

      <div class="fg">
        <div class="flabel"><i class="fa-solid fa-envelope"></i> Email Address</div>
        <input type="email" class="fc" value="{{ $email }}" disabled style="background:var(--gray100)">
      </div>

      <div class="fg">
        <div class="flabel"><i class="fa-solid fa-lock"></i> New Password</div>
        <div class="iw">
          <i class="fa-solid fa-lock ii"></i>
          <input type="password" name="password" id="rpass" class="fc" placeholder="Password (8+, Uppercase, Number)" oninput="checkStrength(this.value)" required>
          <button type="button" class="eye-btn" onclick="toggleEye('rpass','re1')"><i class="fa-solid fa-eye" id="re1"></i></button>
        </div>
        <div class="str-bar">
          <div class="str-seg" id="s1"></div>
          <div class="str-seg" id="s2"></div>
          <div class="str-seg" id="s3"></div>
          <div class="str-seg" id="s4"></div>
        </div>
        <div class="str-txt" id="str-lbl">Enter a password</div>

        <div style="background:var(--gray100);border-radius:8px;padding:10px 12px;margin-top:8px">
          <div id="req-length" style="font-size:.72rem;color:var(--gray400);margin-bottom:3px">○ At least 8 characters</div>
          <div id="req-upper" style="font-size:.72rem;color:var(--gray400);margin-bottom:3px">○ One uppercase letter (A–Z)</div>
          <div id="req-number" style="font-size:.72rem;color:var(--gray400);margin-bottom:3px">○ One number (0–9)</div>
          <div id="req-symbol" style="font-size:.72rem;color:var(--gray400)">○ One symbol (@$!%*#?&amp;) — optional, but recommended</div>
        </div>
      </div>

      <div class="fg">
        <div class="flabel"><i class="fa-solid fa-lock"></i> Confirm New Password</div>
        <div class="iw">
          <i class="fa-solid fa-lock ii"></i>
          <input type="password" name="password_confirmation" id="rpass2" class="fc" placeholder="Re-enter your new password" required>
          <button type="button" class="eye-btn" onclick="toggleEye('rpass2','re2')"><i class="fa-solid fa-eye" id="re2"></i></button>
        </div>
      </div>

      <button type="submit" class="btn" style="margin-top:12px"><i class="fa-solid fa-key"></i> Reset Password</button>
    </form>

    <div class="divider">or</div>
    <div class="form-foot"><a href="{{ route('login') }}"><i class="fa-solid fa-arrow-left"></i> Back to Sign In</a></div>
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

function checkStrength(v){
  const hasLength=v.length>=8;
  const hasUpper=/[A-Z]/.test(v);
  const hasNumber=/[0-9]/.test(v);
  const hasSymbol=/[@$!%*#?&]/.test(v);

  setReq('req-length', hasLength, 'At least 8 characters');
  setReq('req-upper', hasUpper, 'One uppercase letter (A–Z)');
  setReq('req-number', hasNumber, 'One number (0–9)');
  setReq('req-symbol', hasSymbol, 'One symbol (@$!%*#?&) — optional, but recommended');

  let s=0;
  if(hasLength)s++;
  if(hasUpper)s++;
  if(hasNumber)s++;
  if(hasSymbol)s++;
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

function setReq(id, met, label){
  const el=document.getElementById(id);
  el.textContent=(met?'✓ ':'○ ')+label;
  el.style.color=met?'var(--g600)':'var(--gray400)';
}
</script>
@endpush