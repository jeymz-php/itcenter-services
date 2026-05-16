@extends('layouts.app')
@section('title','Admin Login | IT Services System')
@section('content')
@section('body-class', 'auth-page')
@section('auth-bg', true)
<div class="auth-wrap admin-card">
  <div class="admin-head">
    <img src="{{ asset('images/UCC_Logo.png') }}" alt="UCC Logo">
    <h2>Admin Portal</h2>
    <p>IT Services System Administration</p>
    <span class="badge">System Administrator Access</span>
  </div>

  <div class="panel-right" style="padding-top:20px">
    <div class="abox warn" style="margin-bottom:16px">
      <i class="fa-solid fa-triangle-exclamation"></i>
      <div><strong>Restricted Area:</strong> Access limited to authorized administrators only.</div>
    </div>

    @if($errors->has('login'))
      <div class="abox err"><i class="fa-solid fa-circle-xmark"></i> {{ $errors->first('login') }}</div>
    @endif

    <form action="{{ route('admin.login.post') }}" method="POST">
      @csrf
      <div class="fg">
        <div class="flabel"><i class="fa-solid fa-user-shield"></i> Admin ID / Email</div>
        <div class="iw">
          <i class="fa-solid fa-user ii"></i>
          <input type="text" name="admin_id" class="fc" placeholder="Enter admin ID or email" value="{{ old('admin_id') }}" required>
        </div>
      </div>

      <div class="fg">
        <div class="flabel"><i class="fa-solid fa-lock"></i> Password</div>
        <div class="iw">
          <i class="fa-solid fa-lock ii"></i>
          <input type="password" name="password" id="apass" class="fc" placeholder="Enter admin password" required>
          <button type="button" class="eye-btn" onclick="toggleEye('apass','ae1')"><i class="fa-solid fa-eye" id="ae1"></i></button>
        </div>
      </div>

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

      <button type="submit" class="btn"><i class="fa-solid fa-right-to-bracket"></i> Admin Login</button>
    </form>

    <a href="{{ route('login') }}" class="back-link" style="margin-top:16px">
      <i class="fa-solid fa-arrow-left"></i> Back to User Login
    </a>

    <div class="cpr">&copy; {{ date('Y') }} IT Services System Admin Portal</div>
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
</script>
@endpush