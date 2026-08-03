<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ComputerSession;
use App\Models\ServiceRequest;
use App\Models\UserNotification;
use App\Services\ComputerSessionMonitor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(ComputerSessionMonitor $monitor)
    {
        $user = Auth::user();
        $monitor->expireDueSessionsForUser($user);

        $notifications = UserNotification::where('user_id', $user->id)
            ->latest()
            ->paginate(20);

        return view('user.notifications', compact('notifications'));
    }

    public function open(UserNotification $notification)
    {
        $this->authorizeOwner($notification);
        $notification->update(['is_read' => true]);

        return redirect()->to($notification->action_url ?: route('user.notifications.index'));
    }

    public function markRead(UserNotification $notification)
    {
        $this->authorizeOwner($notification);
        $notification->update(['is_read' => true]);

        return response()->json(['ok' => true]);
    }

    public function markAllRead(Request $request)
    {
        UserNotification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'count' => 0]);
        }

        return back()->with('success', 'All notifications marked as read.');
    }

    public function unreadCount()
    {
        return response()->json([
            'count' => UserNotification::where('user_id', Auth::id())
                ->where('is_read', false)
                ->count(),
        ]);
    }

    public function poll(Request $request, ComputerSessionMonitor $monitor)
    {
        $user = Auth::user();
        $monitor->expireDueSessionsForUser($user);

        $lastId = max(0, (int) $request->query('last_id', 0));
        $query = UserNotification::where('user_id', $user->id);

        $new = (clone $query)
            ->where('id', '>', $lastId)
            ->oldest('id')
            ->take(10)
            ->get();

        $visibleRequestIds = collect(explode(',', (string) $request->query('request_ids', '')))
            ->map(fn ($id) => (int) trim($id))
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->take(50)
            ->values();

        $requestUpdates = $visibleRequestIds->isEmpty()
            ? collect()
            : ServiceRequest::query()
                ->with(['computer:id,name', 'computerSession:id,service_request_id,computer_id,duration_minutes,extended_minutes,started_at,ends_at,ended_at,status'])
                ->where('user_id', $user->id)
                ->whereIn('id', $visibleRequestIds)
                ->get()
                ->map(fn (ServiceRequest $serviceRequest) => $this->formatRequest($serviceRequest));

        $activeSession = ComputerSession::query()
            ->with(['computer:id,name', 'serviceRequest:id,request_number,status'])
            ->where('user_id', $user->id)
            ->whereIn('status', ['active', 'extended'])
            ->latest('id')
            ->first();

        $recentNotifications = (clone $query)
            ->latest('id')
            ->take(8)
            ->get()
            ->map(fn (UserNotification $notification) => $this->formatNotification($notification));

        return response()->json([
            'notifications' => $new->map(fn (UserNotification $notification) => $this->formatNotification($notification)),
            'recent_notifications' => $recentNotifications,
            'request_updates' => $requestUpdates,
            'active_session' => $activeSession ? [
                'id'                => $activeSession->id,
                'request_id'        => $activeSession->service_request_id,
                'request_number'    => $activeSession->serviceRequest?->request_number,
                'request_status'    => $activeSession->serviceRequest?->status,
                'session_status'    => $activeSession->status,
                'computer'          => $activeSession->computer?->name,
                'remaining_seconds' => $activeSession->remaining_seconds,
                'total_seconds'     => $activeSession->total_minutes * 60,
                'ends_at'           => $activeSession->ends_at?->format('g:i A'),
                'ends_at_iso'       => $activeSession->ends_at?->toIso8601String(),
            ] : null,
            'unread_count' => (clone $query)->where('is_read', false)->count(),
            // Advance only through the delivered batch so bursts larger than
            // ten notifications are continued on the next poll instead of skipped.
            'last_id'      => $new->isNotEmpty()
                ? (int) $new->max('id')
                : (int) ((clone $query)->max('id') ?? $lastId),
            'server_time'  => now()->toIso8601String(),
        ]);
    }

    private function formatNotification(UserNotification $notification): array
    {
        return [
            'id'         => $notification->id,
            'title'      => $notification->title,
            'type'       => $notification->type,
            'message'    => $notification->message,
            'icon'       => $notification->icon ?? 'fa-bell',
            'action_url' => route('user.notifications.open', $notification),
            'is_read'    => (bool) $notification->is_read,
            'created_at' => $notification->created_at->diffForHumans(),
            'created_at_full' => $notification->created_at->format('M d, Y g:i A'),
        ];
    }

    private function formatRequest(ServiceRequest $serviceRequest): array
    {
        $session = $serviceRequest->computerSession;

        return [
            'id'             => $serviceRequest->id,
            'request_number' => $serviceRequest->request_number,
            'service_type'   => $serviceRequest->service_type,
            'status'         => $serviceRequest->status,
            'status_label'   => strtoupper($serviceRequest->status),
            'updated_at'     => $serviceRequest->updated_at->format('M d, Y g:i A'),
            'updated_at_iso' => $serviceRequest->updated_at->toIso8601String(),
            'session'        => $session ? [
                'status'            => $session->status,
                'computer'          => $serviceRequest->computer?->name,
                'remaining_seconds' => $session->remaining_seconds,
                'total_seconds'     => $session->total_minutes * 60,
                'ends_at'           => $session->ends_at?->format('g:i A'),
                'ended_at'          => $session->ended_at?->format('g:i A'),
            ] : null,
        ];
    }

    private function authorizeOwner(UserNotification $notification): void
    {
        abort_unless($notification->user_id === Auth::id(), 403);
    }
}
