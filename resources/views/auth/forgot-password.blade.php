@extends('layouts.app')
@section('title','Forgot Password | IT Center Services System')
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
    <div class="left-title">Forgot Your<br>Password?</div>
    <div class="left-desc">No problem — enter the email address on your account and we'll send you a link to reset it.</div>
  </div>

  <div class="panel-right">
    <div class="form-hd"><h2>Reset Your Password</h2><p>Enter your registered email address</p></div>

    @if(session('success'))
      <div class="abox ok"><i class="fa-solid fa-circle-check"></i><span>{{ session('success') }}</span></div>
    @endif
    @if($errors->any())
      <div class="abox err"><i class="fa-solid fa-triangle-exclamation"></i><span>{{ $errors->first() }}</span></div>
    @endif

    <form action="{{ route('password.email') }}" method="POST">
      @csrf
      <div class="fg">
        <div class="flabel"><i class="fa-solid fa-envelope"></i> Email Address</div>
        <div class="iw">
          <i class="fa-solid fa-envelope ii"></i>
          <input type="email" name="email" class="fc" placeholder="Enter your registered email" value="{{ old('email') }}" required autofocus>
        </div>
      </div>

      <button type="submit" class="btn"><i class="fa-solid fa-paper-plane"></i> Send Reset Link</button>
    </form>

    <div class="divider">or</div>
    <div class="form-foot"><a href="{{ route('login') }}"><i class="fa-solid fa-arrow-left"></i> Back to Sign In</a></div>
  </div>
</div>
@endsection