@extends('user.requests._layout')
@section('title','Request Details | IT Center')
@section('page-title','Request Details')
@section('page-sub',$r->request_number)

@section('request-content')
<div style="margin-bottom:14px">
  <a href="{{ route('requests.history') }}"
    style="display:inline-flex;align-items:center;gap:6px;color:var(--g700);font-size:.78rem;font-weight:700;text-decoration:none">
    <i class="fa-solid fa-arrow-left"></i> Back to My Requests
  </a>
</div>

<div style="background:var(--white);border:1.5px solid var(--gray200);border-radius:14px;overflow:hidden;box-shadow:var(--shadow-sm);max-width:760px;margin:0 auto">
  @include('user.requests._details', ['r' => $r])
</div>
@endsection
