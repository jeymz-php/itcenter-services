<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Mail\NewMessageMail;
use App\Models\Admin;
use App\Models\AdminNotification;
use App\Models\ChatSession;
use App\Models\Message;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class MessageController extends Controller
{
    // Chat screen for the logged-in user
    public function index(Request $request) {
        $user = Auth::user();

        $openSession = ChatSession::openFor($user->id);

        $messages = $openSession
            ? Message::where('chat_session_id', $openSession->id)->with('senderAdmin')->orderBy('created_at')->get()
            : collect();

        // Mark all admin -> user messages as read now that the user opened the thread
        if ($openSession) {
            Message::where('chat_session_id', $openSession->id)->unreadByUser()->update(['is_read_by_user' => true]);
        }

        // Requests the user can attach a message to (for follow-ups)
        $requests = ServiceRequest::where('user_id', $user->id)
                        ->whereIn('status', ['pending','approved','processing'])
                        ->latest()
                        ->get();

        $lastId          = $messages->max('id') ?? 0;
        $sessionOpen     = (bool) $openSession;
        $conversationEnded = !$sessionOpen && ChatSession::hasEndedSessionFor($user->id);

        return view('user.messages.index', compact('messages', 'requests', 'lastId', 'sessionOpen', 'conversationEnded'));
    }

    // Send a new message (AJAX) — opens a fresh session automatically if the previous one was ended
    public function send(Request $request) {
        $request->validate([
            'body'               => 'required|string|max:2000',
            'service_request_id' => 'nullable|exists:service_requests,id',
        ]);

        $user    = Auth::user();
        $session = ChatSession::getOrOpenFor($user->id);

        $message = Message::create([
            'user_id'             => $user->id,
            'chat_session_id'     => $session->id,
            'service_request_id'  => $request->service_request_id,
            'sender_type'         => 'user',
            'body'                => trim($request->body),
            'is_read_by_user'     => true,
            'is_read_by_admin'    => false,
        ]);

        Cache::forget("typing:user:{$user->id}");

        AdminNotification::notify(
            'new_message',
            'New Message from ' . $user->full_name,
            \Illuminate\Support\Str::limit($message->body, 90),
            $user,
            route('admin.messages.index', ['user' => $user->id]),
            'fa-comment-dots'
        );

        // Email every admin — but only for the FIRST unread message in this
        // session, so a rapid back-and-forth doesn't spam every admin's inbox.
        $isFirstMessageInSession = Message::where('chat_session_id', $session->id)->count() === 1;
        if ($isFirstMessageInSession) {
            $this->notifyAdminsByEmail($user, $message);
        }

        return response()->json([
            'ok'      => true,
            'message' => $this->format($message),
        ]);
    }

    // Poll for new messages since last_id (used for near real-time updates)
    public function poll(Request $request) {
        $user   = Auth::user();
        $lastId = (int) $request->query('last_id', 0);

        $openSession = ChatSession::openFor($user->id);

        $new = $openSession
            ? Message::where('chat_session_id', $openSession->id)->where('id', '>', $lastId)->with('senderAdmin')->orderBy('created_at')->get()
            : collect();

        $new->where('sender_type', 'admin')->each(function ($m) {
            if (!$m->is_read_by_user) $m->update(['is_read_by_user' => true]);
        });

        return response()->json([
            'messages'      => $new->map(fn($m) => $this->format($m)),
            'last_id'       => $openSession ? (Message::where('chat_session_id', $openSession->id)->max('id') ?? $lastId) : $lastId,
            'admin_typing'  => Cache::has("typing:admin:{$user->id}"),
            'session_active'=> (bool) $openSession,
        ]);
    }

    // End the current chat session — clears the active thread for both sides.
    // Old messages are preserved in the database, just no longer shown; the
    // next message sent by either side automatically opens a fresh session.
    public function endSession(Request $request) {
        $user    = Auth::user();
        $session = ChatSession::openFor($user->id);

        if ($session) {
            $session->update(['closed_at' => now(), 'closed_by' => 'user']);
            AdminNotification::notify(
                'chat_ended',
                'Conversation Ended',
                "{$user->full_name} ended the chat conversation.",
                $user,
                route('admin.messages.index', ['user' => $user->id]),
                'fa-comment-slash'
            );
        }

        Cache::forget("typing:user:{$user->id}");
        Cache::forget("typing:admin:{$user->id}");

        return response()->json(['ok' => true]);
    }

    // Typing ping — called while the user is actively composing a message
    public function typing(Request $request) {
        $user = Auth::user();
        Cache::put("typing:user:{$user->id}", true, now()->addSeconds(6));
        return response()->json(['ok' => true]);
    }

    // Lightweight unread-count check for the sidebar/topbar badge
    public function unreadCount() {
        $user = Auth::user();
        $count = Message::where('user_id', $user->id)->inOpenSession()->unreadByUser()->count();
        return response()->json(['count' => $count]);
    }

    private function notifyAdminsByEmail($user, Message $message): void {
        try {
            $admins = Admin::whereNotNull('email')->get();
            foreach ($admins as $admin) {
                Mail::to($admin->email)->send(new NewMessageMail(
                    $admin->admin_id,
                    $user->full_name,
                    $message->body,
                    route('admin.messages.index', ['user' => $user->id]),
                    'Open Conversation'
                ));
            }
        } catch (\Throwable $e) {
            report($e);
        }
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