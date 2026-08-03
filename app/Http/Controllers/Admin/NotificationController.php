<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Services\ComputerSessionMonitor;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    private function scoped()
    {
        $admin = session('admin');
        $query = AdminNotification::query();

        if ($admin->role !== 'super_admin') {
            $query->where(function ($q) use ($admin) {
                $q->where('campus', $admin->campus)->orWhereNull('campus');
            });
        }

        return $query;
    }

    private function syncExpiredSessions(ComputerSessionMonitor $monitor): void
    {
        if ($admin = session('admin')) {
            $monitor->expireDueSessionsForAdmin($admin);
        }
    }

    public function index(ComputerSessionMonitor $monitor)
    {
        if (!session('admin')) {
            abort(403);
        }

        $this->syncExpiredSessions($monitor);
        $this->scoped()->where('is_read', false)->update(['is_read' => true]);
        $notifications = $this->scoped()->latest()->paginate(20);
        $totalCount = $this->scoped()->count();
        $unreadCount = $this->scoped()->where('is_read', false)->count();

        return view('admin.notifications', compact('notifications', 'totalCount', 'unreadCount'));
    }

    public function markRead(AdminNotification $n)
    {
        $admin = session('admin');
        if (!$admin) {
            abort(403);
        }

        if ($admin->role !== 'super_admin' && $n->campus !== null && $n->campus !== $admin->campus) {
            abort(403);
        }

        $n->update(['is_read' => true]);
        return response()->json(['ok' => true]);
    }

    public function markAllRead(Request $request)
    {
        if (!session('admin')) {
            abort(403);
        }

        $this->scoped()->where('is_read', false)->update(['is_read' => true]);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'count' => 0]);
        }

        return back()->with('success', 'All notifications marked as read.');
    }

    public function unreadCount(ComputerSessionMonitor $monitor)
    {
        if (!session('admin')) {
            return response()->json(['count' => 0, 'latest' => null]);
        }

        $this->syncExpiredSessions($monitor);
        $latest = $this->scoped()->where('is_read', false)->latest()->first();

        return response()->json([
            'count'  => $this->scoped()->where('is_read', false)->count(),
            'latest' => $latest ? $this->formatNotification($latest) : null,
        ]);
    }

    public function poll(Request $request, ComputerSessionMonitor $monitor)
    {
        if (!session('admin')) {
            return response()->json([
                'notifications' => [],
                'recent_notifications' => [],
                'unread_count' => 0,
                'last_id' => 0,
            ]);
        }

        // Any open Admin/Super Admin page checks and releases expired sessions.
        $this->syncExpiredSessions($monitor);

        $lastId = max(0, (int) $request->query('last_id', 0));
        $scoped = $this->scoped();

        $new = (clone $scoped)
            ->where('id', '>', $lastId)
            ->oldest('id')
            ->take(10)
            ->get();

        $recent = (clone $scoped)
            ->latest('id')
            ->take(8)
            ->get()
            ->map(fn (AdminNotification $notification) => $this->formatNotification($notification));

        return response()->json([
            'notifications' => $new->map(fn (AdminNotification $notification) => $this->formatNotification($notification)),
            'recent_notifications' => $recent,
            'unread_count' => (clone $scoped)->where('is_read', false)->count(),
            // Advance only through the delivered batch so no notifications
            // are skipped when several updates arrive at the same time.
            'last_id'      => $new->isNotEmpty()
                ? (int) $new->max('id')
                : (int) ((clone $scoped)->max('id') ?? $lastId),
        ]);
    }

    private function formatNotification(AdminNotification $notification): array
    {
        return [
            'id'            => $notification->id,
            'title'         => $notification->title,
            'message'       => $notification->message,
            'type'          => $notification->type,
            'icon'          => $notification->icon ?? 'fa-bell',
            'action_url'    => $notification->action_url ?: route('admin.notifications'),
            'mark_read_url' => route('admin.notifications.read', $notification),
            'is_read'       => (bool) $notification->is_read,
            'created_at'    => $notification->created_at->diffForHumans(),
            'created_at_full' => $notification->created_at->format('M d, Y g:i A'),
        ];
    }
}
