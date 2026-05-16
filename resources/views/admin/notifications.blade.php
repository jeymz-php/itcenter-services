@extends('layouts.app')
@section('title','Notifications | Admin')
@section('body-class','dash-page')
@section('content')

<div class="dash-wrap">
  @include('admin.partials.sidebar')
  <main class="main">
    @include('admin.partials.topbar', ['title'=>'Notifications','sub'=>'All system activity and alerts'])
    <div class="content">

      {{-- HEADER BAR --}}
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:10px">
        <div style="display:flex;gap:8px;flex-wrap:wrap">
          @php
            $unreadCount = \App\Models\AdminNotification::where('is_read',false)->count();
            $totalCount  = \App\Models\AdminNotification::count();
          @endphp
          <div style="background:var(--white);border-radius:10px;padding:10px 16px;border:1.5px solid var(--gray200);box-shadow:var(--shadow-sm);text-align:center;min-width:90px">
            <div style="font-size:1.3rem;font-weight:800;color:var(--gray800)">{{ $totalCount }}</div>
            <div style="font-size:.68rem;color:var(--gray400)">Total</div>
          </div>
          <div style="background:var(--red-bg);border-radius:10px;padding:10px 16px;border:1.5px solid var(--red);text-align:center;min-width:90px">
            <div style="font-size:1.3rem;font-weight:800;color:var(--red)">{{ $unreadCount }}</div>
            <div style="font-size:.68rem;color:var(--red)">Unread</div>
          </div>
        </div>
        <form action="{{ route('admin.notifications.mark-all-read') }}" method="POST">
          @csrf
          <button type="submit"
            style="background:var(--g100);color:var(--g700);border:1.5px solid var(--g300);border-radius:9px;padding:9px 18px;font-size:.78rem;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:6px">
            <i class="fa-solid fa-check-double"></i> Mark All as Read
          </button>
        </form>
      </div>

      {{-- NOTIFICATIONS LIST --}}
      @if($notifications->count())
      <div style="display:flex;flex-direction:column;gap:8px">
        @foreach($notifications as $n)
        <div style="background:{{ !$n->is_read?'var(--white)':'var(--offwhite)' }};border-radius:12px;
          border:1.5px solid {{ !$n->is_read?'var(--g200)':'var(--gray200)' }};
          box-shadow:{{ !$n->is_read?'var(--shadow-sm)':'none' }};
          padding:14px 18px;display:flex;align-items:flex-start;gap:14px;
          transition:all .2s;position:relative;overflow:hidden">

          @if(!$n->is_read)
          <div style="position:absolute;left:0;top:0;bottom:0;width:4px;background:var(--g500);border-radius:12px 0 0 12px"></div>
          @endif

          {{-- Icon --}}
          <div style="width:42px;height:42px;border-radius:11px;flex-shrink:0;
            display:flex;align-items:center;justify-content:center;font-size:.95rem;
            background:{{ !$n->is_read?'var(--g100)':'var(--gray100)' }};
            color:{{ !$n->is_read?'var(--g600)':'var(--gray400)' }}">
            <i class="fa-solid {{ $n->icon ?? 'fa-bell' }}"></i>
          </div>

          {{-- Content --}}
          <div style="flex:1;min-width:0">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;flex-wrap:wrap">
              <div style="font-size:.82rem;font-weight:{{ !$n->is_read?'800':'600' }};color:var(--gray800)">
                {{ $n->title }}
                @if(!$n->is_read)
                <span style="display:inline-block;width:7px;height:7px;border-radius:50%;background:var(--g500);margin-left:5px;vertical-align:middle"></span>
                @endif
              </div>
              <div style="font-size:.68rem;color:var(--gray400);white-space:nowrap;flex-shrink:0">
                {{ $n->created_at->diffForHumans() }}
              </div>
            </div>
            <div style="font-size:.76rem;color:var(--gray600);margin-top:4px;line-height:1.5">
              {{ $n->message }}
            </div>
            @php
              $typeColors = [
                'new_print_request'     => ['var(--blue-bg)','var(--blue)'],
                'new_photocopy_request' => ['var(--orange-bg)','var(--orange)'],
                'new_research_request'  => ['var(--g100)','var(--g700)'],
                'guest_request'         => ['var(--purple-bg,#ede7f6)','var(--purple,#6a1b9a)'],
                'account_approved'      => ['var(--g100)','var(--g600)'],
                'account_rejected'      => ['var(--red-bg)','var(--red)'],
                'account_deactivated'   => ['#f3e5f5','#7b1fa2'],
                'extend_request'        => ['var(--orange-bg)','var(--orange)'],
                'session_ended'         => ['var(--g100)','var(--g700)'],
                'pc_assigned'           => ['var(--g100)','var(--g500)'],
                'deactivate_request'    => ['#f3e5f5','#7b1fa2'],
                'reactivate_request'    => ['var(--g100)','var(--g600)'],
                'delete_request'        => ['var(--red-bg)','var(--red)'],
              ];
              [$tagBg,$tagColor] = $typeColors[$n->type] ?? ['var(--gray100)','var(--gray600)'];
            @endphp
            <div style="margin-top:8px;display:flex;align-items:center;gap:8px;flex-wrap:wrap">
              <span style="font-size:.65rem;font-weight:700;padding:3px 8px;border-radius:6px;background:{{ $tagBg }};color:{{ $tagColor }}">
                {{ strtoupper(str_replace('_',' ',$n->type)) }}
              </span>
              <span style="font-size:.67rem;color:var(--gray400)">
                {{ $n->created_at->format('M d, Y g:i A') }}
              </span>
              @if($n->action_url)
              <a href="{{ $n->action_url }}"
                style="font-size:.68rem;color:var(--g700);font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:4px;margin-left:auto">
                View <i class="fa-solid fa-arrow-right" style="font-size:.6rem"></i>
              </a>
              @endif
            </div>
          </div>

        </div>
        @endforeach
      </div>
      @else
      <div style="background:var(--white);border-radius:14px;padding:48px;text-align:center;border:1.5px solid var(--gray200)">
        <i class="fa-solid fa-bell-slash" style="font-size:2.5rem;color:var(--gray300);display:block;margin-bottom:12px"></i>
        <div style="font-size:.9rem;font-weight:700;color:var(--gray600)">No notifications yet</div>
        <div style="font-size:.78rem;color:var(--gray400);margin-top:5px">System activity will appear here</div>
      </div>
      @endif

      <div style="margin-top:16px">{{ $notifications->links() }}</div>
    </div>
  </main>
</div>
@endsection