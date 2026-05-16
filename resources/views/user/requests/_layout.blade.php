@extends('layouts.app')
@section('body-class','dash-page')
@section('content')
@php $user = Auth::user(); @endphp

<div class="dash-wrap">

  {{-- SIDEBAR: uses the shared user partial --}}
  @include('user.partials.sidebar')

  <main class="main">
    <div class="topbar">
      <div>
        <h1>@yield('page-title')</h1>
        <p>@yield('page-sub')</p>
      </div>
      <div class="topbar-right">
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
      @yield('request-content')
    </div>
  </main>
</div>

@push('scripts')
<script>
(function tick(){
  const n=new Date(), h=n.getHours(), m=n.getMinutes(), s=n.getSeconds();
  const ap=h>=12?'PM':'AM', h12=h%12||12;
  const el=document.getElementById('clock');
  if(el) el.textContent =
    String(h12).padStart(2,'0') + ':' +
    String(m).padStart(2,'0')   + ':' +
    String(s).padStart(2,'0')   + ' ' + ap;
  setTimeout(tick, 1000);
})();
</script>
@endpush

@endsection