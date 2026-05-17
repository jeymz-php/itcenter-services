<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    // Poll for status changes on user's requests
    public function poll(Request $request) {
        $user   = Auth::user();
        $lastId = (int) $request->query('last_id', 0);

        // Find requests whose status changed since last poll
        // We use AdminNotification records that reference this user
        $new = AdminNotification::where('id', '>', $lastId)
                                ->where('notifiable_type', 'App\\Models\\User')
                                ->where('notifiable_id', $user->id)
                                ->latest()
                                ->take(5)
                                ->get();

        // Also check for active PC session
        $session = \App\Models\ComputerSession::where('user_id', $user->id)
                   ->whereIn('status',['active','extended'])
                   ->with('computer')
                   ->first();

        return response()->json([
            'notifications' => $new->map(fn($n) => [
                'id'      => $n->id,
                'title'   => $n->title,
                'message' => $n->message,
                'icon'    => $n->icon ?? 'fa-bell',
            ]),
            'last_id'        => AdminNotification::max('id') ?? 0,
            'active_session' => $session ? [
                'remaining_seconds' => $session->remaining_seconds,
                'computer'          => $session->computer?->name,
                'ends_at'           => $session->ends_at?->format('g:i A'),
            ] : null,
        ]);
    }
}