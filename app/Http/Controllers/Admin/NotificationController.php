<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index() {
        if (!session('admin')) abort(403);
        AdminNotification::where('is_read', false)->update(['is_read' => true]);
        $notifications = AdminNotification::latest()->paginate(20);
        return view('admin.notifications', compact('notifications'));
    }

    public function markRead(AdminNotification $n) {
        $n->update(['is_read' => true]);
        return response()->json(['ok' => true]);
    }

    public function markAllRead() {
        if (!session('admin')) abort(403);
        AdminNotification::where('is_read', false)->update(['is_read' => true]);
        return back()->with('success', 'All notifications marked as read.');
    }

    public function unreadCount() {
        $latest = AdminNotification::where('is_read', false)->latest()->first();
        return response()->json([
            'count'  => AdminNotification::where('is_read', false)->count(),
            'latest' => $latest ? [
                'title'   => $latest->title,
                'message' => $latest->message,
                'icon'    => $latest->icon ?? 'fa-bell',
                'type'    => $latest->type,
            ] : null,
        ]);
    }

    // Real-time poll — returns new notifications since last_id
    public function poll(Request $request) {
        if (!session('admin')) return response()->json(['notifications' => [], 'count' => 0]);

        $lastId = (int) $request->query('last_id', 0);
        $new = AdminNotification::where('id', '>', $lastId)
                                ->where('is_read', false)
                                ->latest()
                                ->take(10)
                                ->get();

        return response()->json([
            'notifications' => $new->map(fn($n) => [
                'id'         => $n->id,
                'title'      => $n->title,
                'message'    => $n->message,
                'type'       => $n->type,
                'icon'       => $n->icon ?? 'fa-bell',
                'action_url' => $n->action_url,
                'created_at' => $n->created_at->diffForHumans(),
            ]),
            'unread_count' => AdminNotification::where('is_read', false)->count(),
            'last_id'      => AdminNotification::max('id') ?? 0,
        ]);
    }
}