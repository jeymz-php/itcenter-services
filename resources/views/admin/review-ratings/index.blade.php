@extends('layouts.app')
@section('title','Review Ratings | Admin')
@section('body-class','dash-page')
@section('content')

<div class="dash-wrap">
  @include('admin.partials.sidebar')
  <main class="main">
    @include('admin.partials.topbar', ['title'=>'Review Ratings','sub'=>'Student and faculty feedback on completed services'])
    <div class="content">

      {{-- STAT CARDS --}}
      <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:18px">
        <div style="background:var(--white);border-radius:14px;border:1.5px solid var(--gray200);padding:16px 18px">
          <div style="font-size:.68rem;color:var(--gray400);font-weight:700;text-transform:uppercase">Total Ratings</div>
          <div style="font-size:1.6rem;font-weight:800;color:var(--gray800)">{{ $stats['total'] }}</div>
        </div>
        <div style="background:var(--white);border-radius:14px;border:1.5px solid var(--gray200);padding:16px 18px">
          <div style="font-size:.68rem;color:var(--gray400);font-weight:700;text-transform:uppercase">Average Rating</div>
          <div style="font-size:1.6rem;font-weight:800;color:#f5a623"><i class="fa-solid fa-star" style="font-size:1.1rem"></i> {{ $stats['average'] }}</div>
        </div>
        <div style="background:var(--white);border-radius:14px;border:1.5px solid var(--gray200);padding:16px 18px">
          <div style="font-size:.68rem;color:var(--gray400);font-weight:700;text-transform:uppercase">5-Star Ratings</div>
          <div style="font-size:1.6rem;font-weight:800;color:var(--g600)">{{ $stats['five_star'] }}</div>
        </div>
        <div style="background:var(--white);border-radius:14px;border:1.5px solid var(--gray200);padding:16px 18px">
          <div style="font-size:.68rem;color:var(--gray400);font-weight:700;text-transform:uppercase">Anonymous</div>
          <div style="font-size:1.6rem;font-weight:800;color:var(--gray600)">{{ $stats['anonymous'] }}</div>
        </div>
      </div>

      <form class="filter-bar" method="GET" action="{{ route('admin.review-ratings.index') }}">
        <div class="sw" style="min-width:120px">
          <select name="stars" class="fs">
            <option value="">All Stars</option>
            @for($i=5;$i>=1;$i--)
            <option value="{{ $i }}" {{ request('stars')==$i?'selected':'' }}>{{ $i }} Star{{ $i>1?'s':'' }}</option>
            @endfor
          </select>
        </div>
        <div class="sw" style="min-width:140px">
          <select name="service_type" class="fs">
            <option value="">All Services</option>
            <option value="printing"  {{ request('service_type')==='printing' ?'selected':'' }}>Printing</option>
            <option value="photocopy" {{ request('service_type')==='photocopy'?'selected':'' }}>Photocopy</option>
            <option value="research"  {{ request('service_type')==='research' ?'selected':'' }}>Research</option>
          </select>
        </div>
        <div class="sw" style="min-width:140px">
          <select name="visibility" class="fs">
            <option value="">Public &amp; Anonymous</option>
            <option value="public"    {{ request('visibility')==='public'   ?'selected':'' }}>Public Only</option>
            <option value="anonymous" {{ request('visibility')==='anonymous'?'selected':'' }}>Anonymous Only</option>
          </select>
        </div>
        @if(session('admin')->role === 'super_admin')
        <div class="sw" style="min-width:160px">
          <select name="campus" class="fs">
            <option value="">All Campuses</option>
            @foreach(config('campuses') as $key => $label)
            <option value="{{ $key }}" {{ request('campus')===$key?'selected':'' }}>{{ $label }}</option>
            @endforeach
          </select>
        </div>
        @endif
        <button type="submit" class="btn-sm"><i class="fa-solid fa-magnifying-glass"></i> Filter</button>
        <a href="{{ route('admin.review-ratings.index') }}" class="btn-outline">Reset</a>
      </form>

      <div style="display:flex;flex-direction:column;gap:12px;margin-top:16px">
        @forelse($ratings as $r)
        <div style="background:var(--white);border-radius:14px;border:1.5px solid var(--gray200);padding:16px 18px">
          <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:10px;flex-wrap:wrap;gap:8px">
            <div>
              <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                <span style="font-size:.86rem;font-weight:800;color:var(--gray800)">
                  {{ $r->display_first_name }}{{ $r->display_last_name ? ' '.$r->display_last_name : '' }}
                </span>
                @if($r->guest_request_id)
                <span class="tag" style="background:#fff8e1;color:#b86a00"><i class="fa-solid fa-user-tag"></i> Guest</span>
                @endif
                @if($r->is_anonymous)
                <span class="tag" style="background:var(--gray100);color:var(--gray600)"><i class="fa-solid fa-user-secret"></i> Anonymous</span>
                @else
                <span class="tag tag-student">Public</span>
                @endif
              </div>
              <div style="font-size:.7rem;color:var(--gray400);margin-top:2px">
                ID: {{ $r->display_id_number }} &middot; {{ $r->display_campus }} &middot;
                {{ ucfirst($r->service_type_value) }} ({{ $r->request_number }})
              </div>
            </div>
            <div style="text-align:right">
              <div style="color:#f5a623;font-size:.95rem">
                @for($i=1;$i<=5;$i++)
                  <i class="fa-{{ $i<=$r->stars?'solid':'regular' }} fa-star"></i>
                @endfor
              </div>
              <div style="font-size:.64rem;color:var(--gray400);margin-top:2px">{{ $r->created_at->format('M d, Y g:i A') }}</div>
            </div>
          </div>

          @if($r->comment)
          <div style="font-size:.8rem;color:var(--gray700);background:var(--gray100);border-radius:8px;padding:10px 12px;margin-bottom:{{ $r->suggestions ? '8px' : '0' }}">
            <div style="font-size:.64rem;color:var(--gray400);font-weight:700;text-transform:uppercase;margin-bottom:3px">Comment</div>
            {{ $r->comment }}
          </div>
          @endif

          @if($r->suggestions)
          <div style="font-size:.8rem;color:var(--gray700);background:var(--g50);border-left:3px solid var(--g400);border-radius:8px;padding:10px 12px">
            <div style="font-size:.64rem;color:var(--g700);font-weight:700;text-transform:uppercase;margin-bottom:3px">Suggestion / Question</div>
            {{ $r->suggestions }}
          </div>
          @endif
        </div>
        @empty
        <div style="text-align:center;padding:50px;color:var(--gray400)">
          <i class="fa-solid fa-star" style="font-size:2rem;margin-bottom:10px;display:block"></i>
          No ratings match these filters yet.
        </div>
        @endforelse
      </div>

      <div style="margin-top:18px">{{ $ratings->links() }}</div>

    </div>
  </main>
</div>
@endsection