<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\Message;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    // Chat screen for the logged-in user
    public function index(Request $request) {
        $user = Auth::user();

        $messages = Message::where('user_id', $user->id)
                        ->with('senderAdmin')
                        ->orderBy('created_at')
                        ->get();

        // Mark all admin -> user messages as read now that the user opened the thread
        Message::where('user_id', $user->id)
               ->unreadByUser()
               ->update(['is_read_by_user' => true]);

        // Requests the user can attach a message to (for follow-ups)
        $requests = ServiceRequest::where('user_id', $user->id)
                        ->whereIn('status', ['pending','approved','processing'])
                        ->latest()
                        ->get();

        $lastId = $messages->max('id') ?? 0;

        return view('user.messages.index', compact('messages', 'requests', 'lastId'));
    }

    // Send a new message (AJAX)
    public function send(Request $request) {
        $request->validate([
            'body'               => 'required|string|max:2000',
            'service_request_id' => 'nullable|exists:service_requests,id',
        ]);

        $user = Auth::user();

        $message = Message::create([
            'user_id'             => $user->id,
            'service_request_id'  => $request->service_request_id,
            'sender_type'         => 'user',
            'body'                => trim($request->body),
            'is_read_by_user'     => true,
            'is_read_by_admin'    => false,
        ]);

        AdminNotification::notify(
            'new_message',
            'New Message from ' . $user->full_name,
            \Illuminate\Support\Str::limit($message->body, 90),
            $user,
            route('admin.messages.index', ['user' => $user->id]),
            'fa-comment-dots'
        );

        return response()->json([
            'ok'      => true,
            'message' => $this->format($message),
        ]);
    }

    // Poll for new messages since last_id (used for near real-time updates)
    public function poll(Request $request) {
        $user   = Auth::user();
        $lastId = (int) $request->query('last_id', 0);

        $new = Message::where('user_id', $user->id)
                    ->where('id', '>', $lastId)
                    ->with('senderAdmin')
                    ->orderBy('created_at')
                    ->get();

        // Any admin message that just arrived is instantly considered read since the thread is open
        $new->where('sender_type', 'admin')->each(function ($m) {
            if (!$m->is_read_by_user) $m->update(['is_read_by_user' => true]);
        });

        return response()->json([
            'messages' => $new->map(fn($m) => $this->format($m)),
            'last_id'  => Message::where('user_id', $user->id)->max('id') ?? $lastId,
        ]);
    }

    // Lightweight unread-count check for the sidebar/topbar badge
    public function unreadCount() {
        $user = Auth::user();
        $count = Message::where('user_id', $user->id)->unreadByUser()->count();
        return response()->json(['count' => $count]);
    }

    private function format(Message $m): array {
        return [
            'id'         => $m->id,
            'sender'     => $m->sender_type,
            'sender_name'=> $m->sender_name,
            'body'       => e($m->body),
            'time'       => $m->created_at->format('g:i A'),
            'date'       => $m->created_at->format('M d, Y'),
            'request_id' => $m->service_request_id,
        ];
    }
}