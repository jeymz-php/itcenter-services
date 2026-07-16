<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GuestRequest;
use App\Models\GuestComputerSession;
use App\Models\Computer;
use App\Models\AdminNotification;
use App\Models\InventoryItem;
use Illuminate\Http\Request;

class GuestRequestController extends Controller
{
    private function guard() { if (!session('admin')) abort(403); return session('admin'); }

    // Regular admins are restricted to guest requests from their own campus; super admins see everyone.
    private function assertInScope($admin, GuestRequest $gr) {
        if ($admin->role !== 'super_admin' && $gr->campus !== $admin->campus) {
            abort(403, 'You can only manage guest requests from your own campus.');
        }
    }

    public function index(Request $request) {
        $admin = $this->guard();
        $query = GuestRequest::query();
        if ($admin->role !== 'super_admin') {
            $query->where('campus', $admin->campus);
        }
        if ($request->search) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('request_number','like',"%$s%")
                  ->orWhere('first_name','like',"%$s%")
                  ->orWhere('last_name','like',"%$s%")
                  ->orWhere('email','like',"%$s%");
            });
        }
        if ($request->status)       $query->where('status', $request->status);
        if ($request->service_type) $query->where('service_type', $request->service_type);
        if ($request->role)         $query->where('role', $request->role);

        $requests = $query->latest()->paginate(20)->withQueryString();

        $countsQuery = GuestRequest::query();
        if ($admin->role !== 'super_admin') $countsQuery->where('campus', $admin->campus);
        $countsBase = $countsQuery->get();
        $counts = [
            'all'        => $countsBase->count(),
            'pending'    => $countsBase->where('status','pending')->count(),
            'approved'   => $countsBase->where('status','approved')->count(),
            'processing' => $countsBase->where('status','processing')->count(),
            'completed'  => $countsBase->where('status','completed')->count(),
            'rejected'   => $countsBase->where('status','rejected')->count(),
        ];
        return view('admin.guest-requests.index', compact('requests','counts'));
    }

    public function show(GuestRequest $guestRequest) {
        $admin = $this->guard();
        $this->assertInScope($admin, $guestRequest);
        $computers = $guestRequest->service_type === 'research'
            ? Computer::where('status','available')->orderBy('sort_order')->get()
            : collect();
        $session = $guestRequest->computerSession;
        return view('admin.guest-requests.show', [
            'gr'        => $guestRequest,
            'computers' => $computers,
            'session'   => $session,
        ]);
    }

    public function approve(GuestRequest $guestRequest) {
        $admin = $this->guard();
        $this->assertInScope($admin, $guestRequest);
        $guestRequest->update([
            'status'      => 'approved',
            'reviewed_by' => session('admin')->id,
            'reviewed_at' => now(),
        ]);
        return back()->with('success', "Guest request {$guestRequest->request_number} approved.");
    }

    public function reject(Request $request, GuestRequest $guestRequest) {
        $admin = $this->guard();
        $this->assertInScope($admin, $guestRequest);
        $request->validate(['admin_note' => 'required|string|max:500']);
        $guestRequest->update([
            'status'      => 'rejected',
            'reviewed_by' => session('admin')->id,
            'reviewed_at' => now(),
            'admin_note'  => $request->admin_note,
        ]);
        return back()->with('success', "Guest request rejected.");
    }

    public function processing(GuestRequest $guestRequest) {
        $admin = $this->guard();
        $this->assertInScope($admin, $guestRequest);
        $guestRequest->update(['status' => 'processing']);
        return back()->with('success', "Marked as processing.");
    }

    public function complete(GuestRequest $guestRequest) {
        $admin = $this->guard();
        $this->assertInScope($admin, $guestRequest);
        $guestRequest->update(['status' => 'completed']);

        if (in_array($guestRequest->service_type, ['printing','photocopy']) && $guestRequest->paper_size) {
            $item = \App\Models\InventoryItem::where('category','paper_size')
                                            ->where('value', $guestRequest->paper_size)->first();
            if ($item && $item->stock > 0) {
                if ($guestRequest->service_type === 'printing'
                    && $guestRequest->detected_pages
                    && $guestRequest->copies) {
                    $reduce = (int)$guestRequest->detected_pages * (int)$guestRequest->copies;
                } else {
                    $reduce = (int)($guestRequest->copies ?? 0);
                }
                $reduce = min($reduce, (int)$item->stock);
                if ($reduce > 0) $item->decrement('stock', $reduce);
            }
        }

        return back()->with('success', "Marked as completed.");
    }

    public function assignPC(Request $request, GuestRequest $guestRequest) {
        $admin = $this->guard();
        $this->assertInScope($admin, $guestRequest);
        $request->validate(['computer_id' => 'required|exists:computers,id']);

        if ($guestRequest->service_type !== 'research') {
            return back()->withErrors(['error' => 'PC assignment is only for research requests.']);
        }

        $computer = Computer::findOrFail($request->computer_id);
        if ($computer->status !== 'available') {
            return back()->withErrors(['error' => 'Selected PC is not available.']);
        }

        $now    = now();
        $endsAt = $now->copy()->addMinutes($guestRequest->duration_minutes);

        $guestRequest->update([
            'status'      => 'processing',
            'computer_id' => $computer->id,
        ]);

        $computer->update(['status' => 'in_use']);

        GuestComputerSession::create([
            'guest_request_id' => $guestRequest->id,
            'computer_id'      => $computer->id,
            'guest_name'       => $guestRequest->full_name,
            'duration_minutes' => $guestRequest->duration_minutes,
            'started_at'       => $now,
            'ends_at'          => $endsAt,
            'status'           => 'active',
        ]);

        AdminNotification::create([
            'type'            => 'pc_assigned',
            'title'           => 'Guest PC Assigned',
            'message'         => "{$guestRequest->full_name} assigned to {$computer->name} for {$guestRequest->duration_minutes} minutes.",
            'notifiable_id'   => 1,
            'notifiable_type' => 'App\\Models\\Admin',
            'action_url'      => route('admin.guest-requests.show', $guestRequest),
            'icon'            => 'fa-desktop',
        ]);

        return back()->with('success', "{$computer->name} assigned. Session started — ends at {$endsAt->format('g:i A')}.");
    }

    public function endSession(GuestRequest $guestRequest) {
        $admin = $this->guard();
        $this->assertInScope($admin, $guestRequest);
        $session = $guestRequest->computerSession;
        if ($session) {
            $session->update(['status' => 'completed', 'ended_at' => now()]);
            if ($session->computer) {
                $session->computer->update(['status' => 'available']);
            }
        }
        $guestRequest->update(['status' => 'completed']);
        return back()->with('success', 'Session ended. Request marked as completed.');
    }

    public function sessionStatus(GuestRequest $guestRequest) {
        $admin = $this->guard();
        $this->assertInScope($admin, $guestRequest);
        $session = $guestRequest->computerSession;
        if (!$session) return response()->json(['error' => 'No session'], 404);
        return response()->json([
            'remaining_seconds' => $session->remaining_seconds,
            'ends_at'           => $session->ends_at?->format('g:i A'),
            'status'            => $session->status,
            'computer'          => $session->computer?->name,
        ]);
    }
}