<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Models\AdminNotification;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use App\Models\Computer;
use App\Models\ComputerSession;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class ServiceRequestController extends Controller
{
    private function guard() {
        if (!session('admin')) abort(403);
        return session('admin');
    }

    // Regular admins are restricted to requests from their own campus's users; super admins see everyone.
    private function assertInScope($admin, ServiceRequest $sr) {
        if ($admin->role !== 'super_admin' && $sr->user->campus !== $admin->campus) {
            abort(403, 'You can only manage requests from your own campus.');
        }
    }

    public function downloadReport(ServiceRequest $serviceRequest) {
        $admin = $this->guard();
        $this->assertInScope($admin, $serviceRequest);

        $r = $serviceRequest->load(['user','computer','computerSession','admin']);

        $pdf = Pdf::loadView('user.requests.pdf-report', compact('r'))
                ->setPaper([0, 0, 226.77, $r->estimatedReceiptHeightPt()]);

        // Stream (not download) so it opens in a new tab for preview first —
        // the browser's own PDF viewer provides the Download button from there.
        return $pdf->stream("Receipt-{$r->request_number}.pdf");
    }

    public function index(Request $request) {
        $admin = $this->guard();
        $query = ServiceRequest::with('user');

        if ($admin->role !== 'super_admin') {
            $query->whereHas('user', fn($u) => $u->where('campus', $admin->campus));
        }

        if ($request->search) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('request_number','like',"%$s%")
                ->orWhereHas('user', fn($u) => $u->where('first_name','like',"%$s%")
                                                    ->orWhere('last_name','like',"%$s%")
                                                    ->orWhere('id_number','like',"%$s%"));
            });
        }
        if ($request->service_type) $query->where('service_type', $request->service_type);
        if ($request->status)       $query->where('status', $request->status);
        if ($request->campus && $admin->role === 'super_admin') $query->whereHas('user', fn($u) => $u->where('campus', $request->campus));

        $requests = $query->latest()->paginate(20)->withQueryString();

        $countsQuery = ServiceRequest::query();
        if ($admin->role !== 'super_admin') {
            $countsQuery->whereHas('user', fn($u) => $u->where('campus', $admin->campus));
        }

        if ($request->service_type) $countsQuery->where('service_type', $request->service_type);
        if ($request->campus && $admin->role === 'super_admin') $countsQuery->whereHas('user', fn($u) => $u->where('campus', $request->campus));

        $countsBase = $countsQuery->get();
        $counts = [
            'all'        => $countsBase->count(),
            'pending'    => $countsBase->where('status','pending')->count(),
            'approved'   => $countsBase->where('status','approved')->count(),
            'processing' => $countsBase->where('status','processing')->count(),
            'completed'  => $countsBase->where('status','completed')->count(),
            'rejected'   => $countsBase->where('status','rejected')->count(),
        ];

        return view('admin.requests.index', compact('requests','counts'));
    }

    public function show(ServiceRequest $serviceRequest) {
        $admin = $this->guard();
        $this->assertInScope($admin, $serviceRequest);
        return view('admin.requests.show', ['sr' => $serviceRequest]);
    }

    public function approve(ServiceRequest $serviceRequest) {
        $admin = $this->guard();
        $this->assertInScope($admin, $serviceRequest);
        $serviceRequest->update([
            'status'      => 'approved',
            'reviewed_by' => session('admin')->id,
            'reviewed_at' => now(),
        ]);
        AdminNotification::notify(
            'request_approved','Request Approved',
            "Request {$serviceRequest->request_number} approved.",
            $serviceRequest->user, route('admin.service-requests.index'), 'fa-circle-check'
        );
        UserNotification::notify(
            $serviceRequest->user,
            'request_approved',
            'Request Approved',
            "Your {$serviceRequest->service_type} request {$serviceRequest->request_number} has been approved.",
            route('requests.show', $serviceRequest),
            'fa-circle-check'
        );
        return back()->with('success',"Request {$serviceRequest->request_number} approved.");
    }

    public function reject(Request $request, ServiceRequest $serviceRequest) {
        $admin = $this->guard();
        $this->assertInScope($admin, $serviceRequest);
        $request->validate(['admin_note' => 'required|string|max:500']);
        $serviceRequest->update([
            'status'      => 'rejected',
            'reviewed_by' => session('admin')->id,
            'reviewed_at' => now(),
            'admin_note'  => $request->admin_note,
        ]);
        UserNotification::notify(
            $serviceRequest->user,
            'request_rejected',
            'Request Rejected',
            "Your {$serviceRequest->service_type} request {$serviceRequest->request_number} was rejected. Reason: {$request->admin_note}",
            route('requests.show', $serviceRequest),
            'fa-circle-xmark'
        );
        return back()->with('success',"Request rejected.");
    }

    public function complete(ServiceRequest $serviceRequest) {
        $admin = $this->guard();
        $this->assertInScope($admin, $serviceRequest);
        $serviceRequest->update(['status' => 'completed']);
        $this->reduceStock($serviceRequest);
        AdminNotification::notify(
            'request_completed','Request Completed',
            "Request {$serviceRequest->request_number} marked as completed.",
            $serviceRequest->user,
            route('admin.service-requests.index'),
            'fa-check-double'
        );
        UserNotification::notify(
            $serviceRequest->user,
            'request_completed',
            'Service Request Completed',
            "Your {$serviceRequest->service_type} request {$serviceRequest->request_number} has been completed.",
            route('requests.show', $serviceRequest),
            'fa-check-double'
        );
        return back()->with('success', "Request marked as completed.");
    }

    public function processing(ServiceRequest $serviceRequest) {
        $admin = $this->guard();
        $this->assertInScope($admin, $serviceRequest);
        $serviceRequest->update(['status' => 'processing']);
        UserNotification::notify(
            $serviceRequest->user,
            'request_processing',
            'Request Is Being Processed',
            "Your {$serviceRequest->service_type} request {$serviceRequest->request_number} is now being processed.",
            route('requests.show', $serviceRequest),
            'fa-gears'
        );
        return back()->with('success',"Request marked as processing.");
    }
    public function assignPC(Request $request, ServiceRequest $serviceRequest) {
        $admin = $this->guard();
        $this->assertInScope($admin, $serviceRequest);
        $request->validate(['computer_id' => 'required|exists:computers,id']);

        $computer = Computer::findOrFail($request->computer_id);
        if ($computer->status !== 'available') {
            return back()->withErrors(['error' => 'Selected PC is not available.']);
        }

        $now     = now();
        $endsAt  = $now->copy()->addMinutes($serviceRequest->duration_minutes);

        $serviceRequest->update([
            'status'      => 'processing',
            'computer_id' => $computer->id,
            'reviewed_by' => session('admin')->id,
            'reviewed_at' => $now,
        ]);

        $computer->update(['status' => 'in_use']);

        $session = ComputerSession::create([
            'service_request_id' => $serviceRequest->id,
            'computer_id'        => $computer->id,
            'user_id'            => $serviceRequest->user_id,
            'duration_minutes'   => $serviceRequest->duration_minutes,
            'started_at'         => $now,
            'ends_at'            => $endsAt,
            'status'             => 'active',
        ]);

        AdminNotification::notify(
            'pc_assigned', 'PC Assigned',
            "{$serviceRequest->user->full_name} assigned to {$computer->name} for {$serviceRequest->duration_minutes} minutes.",
            $serviceRequest->user,
            route('admin.service-requests.show', $serviceRequest),
            'fa-desktop'
        );
        UserNotification::notify(
            $serviceRequest->user,
            'pc_assigned',
            'PC Assigned — Session Started',
            "{$computer->name} has been assigned to your request {$serviceRequest->request_number}. Your {$serviceRequest->duration_minutes}-minute session ends at {$endsAt->format('g:i A')}.",
            route('dashboard'),
            'fa-desktop'
        );

        return back()->with('success', "{$computer->name} assigned. Session started — ends at {$endsAt->format('g:i A')}.");
    }

    public function extendSession(Request $request, ServiceRequest $serviceRequest) {
        $admin = $this->guard();
        $this->assertInScope($admin, $serviceRequest);
        $request->validate(['extend_minutes' => 'required|integer|in:15,30,45,60']);

        $extendMinutes = (int) $request->input('extend_minutes');

        return DB::transaction(function () use ($admin, $serviceRequest, $extendMinutes) {
            // Lock both records so repeated/double clicks cannot apply an extension
            // after another request has already consumed the remaining allowance.
            $lockedRequest = ServiceRequest::whereKey($serviceRequest->id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->assertInScope($admin, $lockedRequest);

            $session = ComputerSession::where('service_request_id', $lockedRequest->id)
                ->lockForUpdate()
                ->first();

            if (!$session || !in_array($session->status, ['active', 'extended'], true)) {
                return back()->withErrors(['error' => 'No active session to extend.']);
            }

            $dailyLimit = ServiceRequest::dailyResearchLimit();
            $usedBefore = ServiceRequest::minutesUsedToday($lockedRequest->user_id);
            $remaining  = max(0, $dailyLimit - $usedBefore);

            if ($extendMinutes > $remaining) {
                $message = $remaining > 0
                    ? "This extension needs {$extendMinutes} minutes, but the user only has {$remaining} minute(s) left of today's {$dailyLimit}-minute Research / PC Lab limit."
                    : "The user has already reached today's {$dailyLimit}-minute Research / PC Lab limit. The limit resets at 12:00 AM.";

                return back()->withErrors(['extend_minutes' => $message]);
            }

            $newEndsAt = $session->ends_at->copy()->addMinutes($extendMinutes);
            $newExtendedMinutes = (int) $session->extended_minutes + $extendMinutes;

            $session->update([
                'ends_at'          => $newEndsAt,
                'extended_minutes' => $newExtendedMinutes,
                'status'           => 'extended',
            ]);

            $usedAfter = $usedBefore + $extendMinutes;

            AdminNotification::notify(
                'session_extended', 'Session Extended',
                "{$lockedRequest->user->full_name}'s session was extended by {$extendMinutes} min on {$session->computer->name}. Daily Research / PC Lab usage: {$usedAfter}/{$dailyLimit} min.",
                $lockedRequest->user,
                route('admin.service-requests.show', $lockedRequest),
                'fa-clock'
            );
            UserNotification::notify(
                $lockedRequest->user,
                'session_extended',
                'PC Session Extended',
                "Your session on {$session->computer->name} was extended by {$extendMinutes} minutes. It now ends at {$newEndsAt->format('g:i A')}. Today's Research / PC Lab usage is {$usedAfter}/{$dailyLimit} minutes.",
                route('dashboard'),
                'fa-clock'
            );

            return back()->with(
                'success',
                "Session extended by {$extendMinutes} minutes. New end: {$newEndsAt->format('g:i A')}. Daily usage: {$usedAfter}/{$dailyLimit} minutes."
            );
        });
    }

    public function endSession(ServiceRequest $serviceRequest) {
        $admin = $this->guard();
        $this->assertInScope($admin, $serviceRequest);
        $session = $serviceRequest->computerSession;
        if ($session) {
            $session->update(['status' => 'completed', 'ended_at' => now()]);
            if ($session->computer) {
                $session->computer->update(['status' => 'available']);
            }
        }
        $serviceRequest->update(['status' => 'completed']);
        $this->reduceStock($serviceRequest);

        AdminNotification::notify(
            'session_ended','Session Ended',
            "{$serviceRequest->user->full_name} session ended on {$session?->computer?->name}.",
            $serviceRequest->user,
            route('admin.service-requests.index'),
            'fa-desktop'
        );
        UserNotification::notify(
            $serviceRequest->user,
            'session_ended',
            'PC Session Ended',
            "Your Research / PC Lab session for {$serviceRequest->request_number} has ended and the request is now completed.",
            route('requests.show', $serviceRequest),
            'fa-desktop'
        );
        return back()->with('success', 'Session ended. Request marked as completed.');
    }

    public function sessionStatus(ServiceRequest $serviceRequest, \App\Services\ComputerSessionMonitor $monitor) {
        $admin = $this->guard();
        $this->assertInScope($admin, $serviceRequest);

        $session = $serviceRequest->computerSession;
        if (!$session) {
            return response()->json(['error' => 'No session found'], 404);
        }

        if (in_array($session->status, ['active', 'extended'], true)
            && $session->ends_at
            && $session->ends_at->isPast()) {
            $monitor->expireRegisteredSession($session->id);
            $session->refresh();
        }

        return response()->json([
            'remaining_seconds' => $session->remaining_seconds,
            'ends_at'           => $session->ends_at?->format('g:i A'),
            'status'            => $session->status,
            'computer'          => $session->computer?->name,
            'extended_minutes'  => $session->extended_minutes,
        ]);
    }

    public function destroy(Request $request, ServiceRequest $serviceRequest) {
        $admin = $this->guard();
        if ($admin->role !== 'super_admin' && $serviceRequest->user?->campus !== $admin->campus) {
            abort(403, 'You can only manage requests from your own campus.');
        }

        if (!in_array($serviceRequest->status, ['rejected', 'completed'])) {
            return back()->withErrors([
                'error' => "Only rejected or completed requests can be deleted. This request is currently \"{$serviceRequest->status}\" — reject or complete it first."
            ]);
        }

        // Clean up any uploaded file so it doesn't sit orphaned on disk.
        if ($serviceRequest->file_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($serviceRequest->file_path);
        }

        $number = $serviceRequest->request_number;
        $serviceRequest->delete();

        return back()->with('success', "Request {$number} deleted.");
    }

    private function reduceStock(ServiceRequest $sr): void
    {
        if (!in_array($sr->service_type, ['printing','photocopy'])) return;
        if (!$sr->paper_size) return;

        $item = \App\Models\InventoryItem::where('category','paper_size')
                                        ->where('value', $sr->paper_size)
                                        ->first();
        if (!$item || $item->stock <= 0) return;

        // For printing: pages × copies; for photocopy: just copies
        if ($sr->service_type === 'printing' && $sr->detected_pages && $sr->copies) {
            $reduce = (int)$sr->detected_pages * (int)$sr->copies;
        } elseif ($sr->copies) {
            $reduce = (int)$sr->copies;
        } else {
            return;
        }

        $reduce = min($reduce, (int)$item->stock);
        $item->decrement('stock', $reduce);
    }
}