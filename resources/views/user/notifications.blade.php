@extends('user.requests._layout')
@section('title','Notifications | IT Center')
@section('page-title','Notifications')
@section('page-sub','Account updates, request status changes, and PC session alerts')

@section('request-content')
@php
  $unreadCount = \App\Models\UserNotification::where('user_id', Auth::id())->where('is_read', false)->count();
  $typeStyles = [
    'account_approved'       => ['var(--g100)', 'var(--g700)'],
    'request_approved'       => ['var(--blue-bg)', 'var(--blue)'],
    'request_processing'     => ['var(--orange-bg)', 'var(--orange)'],
    'request_completed'      => ['var(--g100)', 'var(--g700)'],
    'request_rejected'       => ['var(--red-bg)', 'var(--red)'],
    'pc_assigned'            => ['var(--blue-bg)', 'var(--blue)'],
    'session_extended'       => ['var(--orange-bg)', 'var(--orange)'],
    'session_ended'          => ['var(--g100)', 'var(--g700)'],
    'session_expired'        => ['#fff3e0', '#d66a00'],
    'research_restricted'    => ['var(--red-bg)', 'var(--red)'],
    'research_unrestricted'  => ['var(--g100)', 'var(--g700)'],
  ];
@endphp

<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:16px">
  <div style="display:flex;gap:10px;flex-wrap:wrap">
    <div style="background:var(--white);border:1.5px solid var(--gray200);border-radius:11px;padding:10px 16px;min-width:100px;text-align:center;box-shadow:var(--shadow-sm)">
      <div style="font-size:1.25rem;font-weight:800;color:var(--gray800)">{{ $notifications->total() }}</div>
      <div style="font-size:.68rem;color:var(--gray400)">Total</div>
    </div>
    <div style="background:{{ $unreadCount ? 'var(--red-bg)' : 'var(--g100)' }};border:1.5px solid {{ $unreadCount ? 'var(--red)' : 'var(--g300)' }};border-radius:11px;padding:10px 16px;min-width:100px;text-align:center">
      <div style="font-size:1.25rem;font-weight:800;color:{{ $unreadCount ? 'var(--red)' : 'var(--g700)' }}">{{ $unreadCount }}</div>
      <div style="font-size:.68rem;color:{{ $unreadCount ? 'var(--red)' : 'var(--g600)' }}">Unread</div>
    </div>
  </div>

  @if($unreadCount)
  <form action="{{ route('user.notifications.mark-all-read') }}" method="POST">
    @csrf
    <button type="submit" class="modal-btn secondary" style="display:flex;align-items:center;gap:7px;border:1.5px solid var(--gray200)">
      <i class="fa-solid fa-check-double"></i> Mark All as Read
    </button>
  </form>
  @endif
</div>

<div class="abox info" style="margin-bottom:16px">
  <i class="fa-solid fa-bell"></i>
  <div>New request updates and Research / PC Lab session alerts appear here and as real-time pop-up notifications while you are using the system.</div>
</div>

@if($notifications->count())
<div style="display:flex;flex-direction:column;gap:9px">
  @foreach($notifications as $notification)
    @php [$tagBg, $tagColor] = $typeStyles[$notification->type] ?? ['var(--gray100)', 'var(--gray600)']; @endphp
    <a href="{{ route('user.notifications.open', $notification) }}"
       style="text-decoration:none;background:{{ $notification->is_read ? 'var(--offwhite)' : 'var(--white)' }};border:1.5px solid {{ $notification->is_read ? 'var(--gray200)' : 'var(--g300)' }};border-radius:13px;padding:14px 17px;display:flex;gap:13px;align-items:flex-start;box-shadow:{{ $notification->is_read ? 'none' : 'var(--shadow-sm)' }};position:relative;overflow:hidden">
      @unless($notification->is_read)
      <span style="position:absolute;left:0;top:0;bottom:0;width:4px;background:var(--g500)"></span>
      @endunless

      <span style="width:42px;height:42px;border-radius:11px;display:flex;align-items:center;justify-content:center;flex-shrink:0;background:{{ $tagBg }};color:{{ $tagColor }}">
        <i class="fa-solid {{ $notification->icon ?? 'fa-bell' }}"></i>
      </span>

      <span style="flex:1;min-width:0">
        <span style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;flex-wrap:wrap">
          <strong style="font-size:.82rem;color:var(--gray800)">{{ $notification->title }}</strong>
          <small style="font-size:.66rem;color:var(--gray400);white-space:nowrap">{{ $notification->created_at->diffForHumans() }}</small>
        </span>
        <span style="display:block;font-size:.75rem;color:var(--gray600);line-height:1.55;margin-top:4px">{{ $notification->message }}</span>
        <span style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-top:8px">
          <span style="font-size:.63rem;font-weight:700;padding:3px 8px;border-radius:6px;background:{{ $tagBg }};color:{{ $tagColor }}">{{ strtoupper(str_replace('_', ' ', $notification->type)) }}</span>
          <span style="font-size:.65rem;color:var(--gray400)">{{ $notification->created_at->format('M d, Y g:i A') }}</span>
          @if($notification->action_url)
          <span style="margin-left:auto;font-size:.68rem;font-weight:700;color:var(--g700)">Open <i class="fa-solid fa-arrow-right"></i></span>
          @endif
        </span>
      </span>
    </a>
  @endforeach
</div>
@else
<div style="background:var(--white);border:1.5px solid var(--gray200);border-radius:14px;padding:50px 24px;text-align:center;color:var(--gray400)">
  <i class="fa-regular fa-bell-slash" style="font-size:2.4rem;display:block;margin-bottom:12px"></i>
  <div style="font-size:.88rem;font-weight:800;color:var(--gray600)">No notifications yet</div>
  <div style="font-size:.75rem;margin-top:5px">Your account and service updates will appear here.</div>
</div>
@endif

<div style="margin-top:16px">{{ $notifications->links() }}</div>
@endsection
