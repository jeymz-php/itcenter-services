@php
  $notificationUser = $user ?? Auth::user();
  $topbarUnreadNotifications = \App\Models\UserNotification::where('user_id', $notificationUser->id)
      ->where('is_read', false)
      ->count();
@endphp
<a href="{{ route('user.notifications.index') }}"
   class="notif-wrap floating-notification-toggle"
   data-user-notification-toggle
   aria-label="Open notifications"
   aria-haspopup="true"
   aria-expanded="false"
   title="Notifications">
  <i class="fa-solid fa-bell" style="font-size:1.05rem"></i>
  <span class="notif-badge" data-user-notif-badge style="{{ $topbarUnreadNotifications ? '' : 'display:none' }}">
    {{ $topbarUnreadNotifications }}
  </span>
</a>
