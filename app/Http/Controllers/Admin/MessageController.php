<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\NewMessageMail;
use App\Models\ChatSession;
use App\Models\Message;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;    

class MessageController extends Controller
{
    private function guard() {
        if (!session('admin')) abort(403);
        return session('admin');
    }

    // Campus-restricted admins only see/message users from their own campus.
    // Super admins see everyone.
    private function inScope($admin, User $user): bool {
        return $admin->role === 'super_admin' || $user->campus === $admin->campus;
    }

    // Inbox: list of conversations (one per user with an OPEN session), optionally with one open
    public function index(Request $request) {
        $admin = $this->guard();

        // Aggregate on messages that belong to a currently-open chat session only —
        // ended conversations drop out of the list entirely until a new message
        // (from either side) opens a fresh session for that user.
        $agg = Message::join('chat_sessions', 'chat_sessions.id', '=', 'messages.chat_session_id')
            ->whereNull('chat_sessions.closed_at')
            ->selectRaw('messages.user_id, MAX(messages.created_at) as last_message_at')
            ->selectRaw('SUM(CASE WHEN messages.sender_type = "user" AND messages.is_read_by_admin = 0 THEN 1 ELSE 0 END) as unread_count')
            ->groupBy('messages.user_id')
            ->orderByDesc('last_message_at')
            ->get();

        $userQuery = User::whereIn('id', $agg->pluck('user_id'));
        if ($admin->role !== 'super_admin') {
            $userQuery->where('campus', $admin->campus);
        }
        $usersById = $userQuery->get()->keyBy('id');

        $conversations = $agg->map(function ($row) use ($usersById) {
                $u = $usersById->get($row->user_id);
                if (!$u) return null; // filtered out by campus scope, or user deleted
                $u->last_message_at = $row->last_message_at;
                $u->unread_count    = $row->unread_count;
                return $u;
            })
            ->filter()
            ->values();

        $activeUserId = (int) $request->query('user', $conversations->first()->id ?? 0);
        $activeUser   = $activeUserId ? User::find($activeUserId) : null;

        if ($activeUser && !$this->inScope($admin, $activeUser)) {
            abort(403, 'You can only message users from your own campus.');
        }

        // If the admin picked a user from "New Chat" who has no open conversation yet,
        // pin them at the top of the list so it's visible even before the first message.
        if ($activeUser && !$conversations->contains('id', $activeUser->id)) {
            $activeUser->last_message_at = null;
            $activeUser->unread_count    = 0;
            $conversations = $conversations->prepend($activeUser);
        }

        $openSession = $activeUser ? ChatSession::openFor($activeUser->id) : null;

        $messages = $openSession
            ? Message::where('chat_session_id', $openSession->id)->with('senderAdmin')->orderBy('created_at')->get()
            : collect();

        if ($openSession) {
            Message::where('chat_session_id', $openSession->id)->unreadByAdmin()->update(['is_read_by_admin' => true]);
        }

        $requests = $activeUser
            ? ServiceRequest::where('user_id', $activeUser->id)->latest()->take(20)->get()
            : collect();

        $lastId            = $messages->max('id') ?? 0;
        $sessionOpen       = (bool) $openSession;
        $conversationEnded = $activeUser && !$sessionOpen && ChatSession::hasEndedSessionFor($activeUser->id);

        return view('admin.messages.index', compact(
            'conversations', 'activeUser', 'messages', 'requests', 'lastId', 'sessionOpen', 'conversationEnded'
        ));
    }

    // Search users to start a new conversation with (AJAX, typeahead) — campus-restricted
    public function searchUsers(Request $request) {
        $admin = $this->guard();

        $q = trim((string) $request->query('q', ''));

        $query = User::where('status', '!=', 'archived')
            ->when($admin->role !== 'super_admin', fn($q2) => $q2->where('campus', $admin->campus));

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('first_name', 'like', "%$q%")
                ->orWhere('last_name', 'like', "%$q%")
                ->orWhere('id_number', 'like', "%$q%")
                ->orWhere('email', 'like', "%$q%");
            });
        }

        $users = $query->orderBy('first_name')->take($q === '' ? 30 : 10)
                    ->get(['id','first_name','last_name','id_number','user_type','status','campus']);

        return response()->json([
            'users' => $users->map(fn($u) => [
                'id'        => $u->id,
                'name'      => $u->first_name . ' ' . $u->last_name,
                'id_number' => $u->id_number,
                'user_type' => ucfirst(str_replace('_',' ',$u->user_type)),
                'status'    => $u->status,
                'campus'    => config('campuses.'.$u->campus, $u->campus),
            ]),
        ]);
    }

    // Send a reply as the logged-in admin (AJAX) — opens a fresh session automatically
    // if the previous one was ended
    public function send(Request $request) {
        $admin = $this->guard();

        $request->validate([
            'user_id'            => 'required|exists:users,id',
            'body'               => 'required|string|max:2000',
            'service_request_id' => 'nullable|exists:service_requests,id',
        ]);

        $user = User::findOrFail($request->user_id);
        if (!$this->inScope($admin, $user)) abort(403, 'You can only message users from your own campus.');

        $session = ChatSession::getOrOpenFor($user->id);

        $message = Message::create([
            'user_id'            => $user->id,
            'chat_session_id'    => $session->id,
            'service_request_id' => $request->service_request_id,
            'sender_type'        => 'admin',
            'sender_admin_id'    => $admin->id,
            'body'               => trim($request->body),
            'is_read_by_admin'   => true,
            'is_read_by_user'    => false,
        ]);

        Cache::forget("typing:admin:{$user->id}");

        // Note: no AdminNotification is created here on purpose. The admin
        // notification bell/list queries aren't scoped by intended recipient,
        // so a notification created here (meant for the student) was also
        // showing up in the sending admin's own bell as a confusing
        // self-notification. The student is still covered by: the live chat
        // poll (if their thread is open), the unread badge on their Messages
        // nav link, and the email notification sent below.

        $isFirstMessageInSession = Message::where('chat_session_id', $session->id)->count() === 1;
        if ($isFirstMessageInSession && $user->email) {
            try {
                Mail::to($user->email)->send(new NewMessageMail(
                    $user->first_name,
                    'IT Center Support',
                    $message->body,
                    route('user.messages.index'),
                    'Open Conversation'
                ));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $message->load('senderAdmin');

        return response()->json(['ok' => true, 'message' => $this->format($message)]);
    }

    // Poll: new messages in the currently open thread + overall unread conversation count
    public function poll(Request $request) {
        if (!session('admin')) return response()->json(['messages' => [], 'unread_conversations' => 0]);
        $admin = session('admin');

        $userId = (int) $request->query('user_id', 0);
        $lastId = (int) $request->query('last_id', 0);

        $new = collect();
        $userTyping  = false;
        $sessionOpen = false;

        if ($userId) {
            $user = User::find($userId);
            if ($user && $this->inScope($admin, $user)) {
                $openSession = ChatSession::openFor($userId);
                $sessionOpen = (bool) $openSession;

                if ($openSession) {
                    $new = Message::where('chat_session_id', $openSession->id)->where('id', '>', $lastId)
                            ->with('senderAdmin')->orderBy('created_at')->get();

                    $new->where('sender_type', 'user')->each(function ($m) {
                        if (!$m->is_read_by_admin) $m->update(['is_read_by_admin' => true]);
                    });
                }

                $userTyping = Cache::has("typing:user:{$userId}");
            }
        }

        $unreadQuery = Message::join('chat_sessions', 'chat_sessions.id', '=', 'messages.chat_session_id')
            ->whereNull('chat_sessions.closed_at')
            ->where('messages.sender_type', 'user')
            ->where('messages.is_read_by_admin', false);
        if ($admin->role !== 'super_admin') {
            $unreadQuery->whereIn('messages.user_id', User::where('campus', $admin->campus)->pluck('id'));
        }
        $unreadConversations = $unreadQuery->distinct('messages.user_id')->count('messages.user_id');

        return response()->json([
            'messages'              => $new->map(fn($m) => $this->format($m)),
            'last_id'               => $userId ? (Message::where('user_id', $userId)->max('id') ?? $lastId) : $lastId,
            'unread_conversations'  => $unreadConversations,
            'user_typing'           => $userTyping,
            'session_active'        => $sessionOpen,
        ]);
    }

    // Typing ping — called while the admin is actively composing a reply
    public function typing(Request $request) {
        $admin = $this->guard();

        $request->validate(['user_id' => 'required|exists:users,id']);
        $user = User::findOrFail($request->user_id);
        if (!$this->inScope($admin, $user)) abort(403);

        Cache::put("typing:admin:{$request->user_id}", $admin->admin_id, now()->addSeconds(6));
        return response()->json(['ok' => true]);
    }

    // End the current chat session with a user — clears the active thread for both
    // sides. Old messages are preserved in the database; the next message sent by
    // either side automatically opens a fresh session.
    public function endSession(Request $request) {
        $admin = $this->guard();

        $request->validate(['user_id' => 'required|exists:users,id']);
        $user = User::findOrFail($request->user_id);
        if (!$this->inScope($admin, $user)) abort(403);

        $session = ChatSession::openFor($user->id);
        if ($session) {
            $session->update([
                'closed_at'          => now(),
                'closed_by'          => 'admin',
                'closed_by_admin_id' => $admin->id,
            ]);
        }

        Cache::forget("typing:admin:{$user->id}");
        Cache::forget("typing:user:{$user->id}");

        return response()->json(['ok' => true]);
    }

    public function unreadCount() {
        if (!session('admin')) return response()->json(['count' => 0]);
        $admin = session('admin');

        $query = Message::join('chat_sessions', 'chat_sessions.id', '=', 'messages.chat_session_id')
            ->whereNull('chat_sessions.closed_at')
            ->where('messages.sender_type', 'user')
            ->where('messages.is_read_by_admin', false);
        if ($admin->role !== 'super_admin') {
            $query->whereIn('messages.user_id', User::where('campus', $admin->campus)->pluck('id'));
        }
        $count = $query->distinct('messages.user_id')->count('messages.user_id');

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