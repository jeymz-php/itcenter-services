<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\Message;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    // Inbox: list of conversations (one per user with messages), optionally with one open
    public function index(Request $request) {
        if (!session('admin')) abort(403);

        // Aggregate on the messages table only (avoids MySQL's ONLY_FULL_GROUP_BY
        // rejecting users.* alongside a GROUP BY on users.id), then attach the
        // matching User records afterwards.
        $agg = Message::selectRaw('user_id, MAX(created_at) as last_message_at')
            ->selectRaw('SUM(CASE WHEN sender_type = "user" AND is_read_by_admin = 0 THEN 1 ELSE 0 END) as unread_count')
            ->groupBy('user_id')
            ->orderByDesc('last_message_at')
            ->get();

        $usersById = User::whereIn('id', $agg->pluck('user_id'))->get()->keyBy('id');

        $conversations = $agg->map(function ($row) use ($usersById) {
                $u = $usersById->get($row->user_id);
                if (!$u) return null;
                $u->last_message_at = $row->last_message_at;
                $u->unread_count    = $row->unread_count;
                return $u;
            })
            ->filter()
            ->values();

        $activeUserId = (int) $request->query('user', $conversations->first()->id ?? 0);
        $activeUser   = $activeUserId ? User::find($activeUserId) : null;

        $messages = collect();
        if ($activeUser) {
            $messages = Message::where('user_id', $activeUser->id)
                            ->with('senderAdmin')
                            ->orderBy('created_at')
                            ->get();

            Message::where('user_id', $activeUser->id)->unreadByAdmin()->update(['is_read_by_admin' => true]);
        }

        $requests = $activeUser
            ? ServiceRequest::where('user_id', $activeUser->id)->latest()->take(20)->get()
            : collect();

        $lastId = $messages->max('id') ?? 0;

        return view('admin.messages.index', compact('conversations', 'activeUser', 'messages', 'requests', 'lastId'));
    }

    // Send a reply as the logged-in admin (AJAX)
    public function send(Request $request) {
        if (!session('admin')) abort(403);

        $request->validate([
            'user_id'            => 'required|exists:users,id',
            'body'               => 'required|string|max:2000',
            'service_request_id' => 'nullable|exists:service_requests,id',
        ]);

        $admin = session('admin');
        $user  = User::findOrFail($request->user_id);

        $message = Message::create([
            'user_id'            => $user->id,
            'service_request_id' => $request->service_request_id,
            'sender_type'        => 'admin',
            'sender_admin_id'    => $admin->id,
            'body'               => trim($request->body),
            'is_read_by_admin'   => true,
            'is_read_by_user'    => false,
        ]);

        AdminNotification::notify(
            'new_message',
            'New Message from IT Center',
            \Illuminate\Support\Str::limit($message->body, 90),
            $user,
            route('user.messages.index'),
            'fa-comment-dots'
        );

        $message->load('senderAdmin');

        return response()->json(['ok' => true, 'message' => $this->format($message)]);
    }

    // Poll: new messages in the currently open thread + overall unread conversation count
    public function poll(Request $request) {
        if (!session('admin')) return response()->json(['messages' => [], 'unread_conversations' => 0]);

        $userId = (int) $request->query('user_id', 0);
        $lastId = (int) $request->query('last_id', 0);

        $new = collect();
        if ($userId) {
            $new = Message::where('user_id', $userId)
                        ->where('id', '>', $lastId)
                        ->with('senderAdmin')
                        ->orderBy('created_at')
                        ->get();

            $new->where('sender_type', 'user')->each(function ($m) {
                if (!$m->is_read_by_admin) $m->update(['is_read_by_admin' => true]);
            });
        }

        $unreadConversations = Message::unreadByAdmin()->pluck('user_id')->unique()->count();

        return response()->json([
            'messages'              => $new->map(fn($m) => $this->format($m)),
            'last_id'               => $userId ? (Message::where('user_id', $userId)->max('id') ?? $lastId) : $lastId,
            'unread_conversations'  => $unreadConversations,
        ]);
    }

    public function unreadCount() {
        if (!session('admin')) return response()->json(['count' => 0]);
        $count = Message::unreadByAdmin()->pluck('user_id')->unique()->count();
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