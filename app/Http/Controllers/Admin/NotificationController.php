<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;

class NotificationController extends Controller
{
    public function index() {
        if (!session('admin')) abort(403);
        $notifications = AdminNotification::latest()->paginate(30);
        AdminNotification::where('is_read', false)->update(['is_read' => true]);
        return view('admin.notifications', compact('notifications'));
    }

    public function markRead(AdminNotification $notification) {
        $notification->update(['is_read' => true]);
        return response()->json(['ok' => true]);
    }

    public function markAllRead() {
        if (!session('admin')) abort(403);
        \App\Models\AdminNotification::where('is_read', false)->update(['is_read' => true]);
        return back()->with('success', 'All notifications marked as read.');
    }

    public function unreadCount() {
        $latest = AdminNotification::where('is_read', false)->latest()->first();
        return response()->json([
            'count'  => AdminNotification::where('is_read', false)->count(),
            'latest' => $latest ? ['title' => $latest->title, 'message' => $latest->message] : null,
        ]);
    }
}