<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Models\AdminNotification;
use Illuminate\Http\Request;
use App\Models\Computer;
use App\Models\ComputerSession;
use Barryvdh\DomPDF\Facade\Pdf;

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
        return back()->with('success', "Request marked as completed.");
    }

    public function processing(ServiceRequest $serviceRequest) {
        $admin = $this->guard();
        $this->assertInScope($admin, $serviceRequest);
        $serviceRequest->update(['status' => 'processing']);
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

        return back()->with('success', "{$computer->name} assigned. Session started — ends at {$endsAt->format('g:i A')}.");
    }

    public function extendSession(Request $request, ServiceRequest $serviceRequest) {
        $admin = $this->guard();
        $this->assertInScope($admin, $serviceRequest);
        $request->validate(['extend_minutes' => 'required|integer|in:15,30,45,60']);

        $session = $serviceRequest->computerSession;
        if (!$session || !in_array($session->status, ['active','extended'])) {
            return back()->withErrors(['error' => 'No active session to extend.']);
        }

        $newEndsAt = $session->ends_at->addMinutes($request->extend_minutes);
        $session->update([
            'ends_at'          => $newEndsAt,
            'extended_minutes' => $session->extended_minutes + $request->extend_minutes,
            'status'           => 'extended',
        ]);

        AdminNotification::notify(
            'session_extended','Session Extended',
            "{$serviceRequest->user->full_name} extended session by {$request->extend_minutes} min on {$session->computer->name}.",
            $serviceRequest->user,
            route('admin.service-requests.show', $serviceRequest),
            'fa-clock'
        );

        return back()->with('success', "Session extended by {$request->extend_minutes} minutes. New end: {$newEndsAt->format('g:i A')}.");
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
        return back()->with('success', 'Session ended. Request marked as completed.');
    }

    public function sessionStatus(ServiceRequest $serviceRequest) {
        // AJAX endpoint for real-time timer
        $session = $serviceRequest->computerSession;
        if (!$session) {
            return response()->json(['error' => 'No session found'], 404);
        }
        return response()->json([
            'remaining_seconds' => $session->remaining_seconds,
            'ends_at'           => $session->ends_at?->format('g:i A'),
            'status'            => $session->status,
            'computer'          => $session->computer?->name,
            'extended_minutes'  => $session->extended_minutes,
        ]);
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