<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Models\AdminNotification;
use Illuminate\Http\Request;
use App\Models\Computer;
use App\Models\ComputerSession;

class ServiceRequestController extends Controller
{
    private function guard() {
        if (!session('admin')) abort(403);
    }

    public function index(Request $request) {
        $this->guard();
        $query = ServiceRequest::with('user');

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
        if ($request->campus)       $query->whereHas('user', fn($u) => $u->where('campus', $request->campus));

        $requests = $query->latest()->paginate(20)->withQueryString();

        $counts = [
            'all'        => ServiceRequest::count(),
            'pending'    => ServiceRequest::where('status','pending')->count(),
            'approved'   => ServiceRequest::where('status','approved')->count(),
            'processing' => ServiceRequest::where('status','processing')->count(),
            'completed'  => ServiceRequest::where('status','completed')->count(),
            'rejected'   => ServiceRequest::where('status','rejected')->count(),
        ];

        return view('admin.requests.index', compact('requests','counts'));
    }

    public function show(ServiceRequest $serviceRequest) {
        $this->guard();
        return view('admin.requests.show', ['sr' => $serviceRequest]);
    }

    public function approve(ServiceRequest $serviceRequest) {
        $this->guard();
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
        $this->guard();
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
        $this->guard();
        $serviceRequest->update(['status' => 'completed']);
        $this->reduceStock($serviceRequest);
        return back()->with('success', "Request marked as completed.");
    }

    public function processing(ServiceRequest $serviceRequest) {
        $this->guard();
        $serviceRequest->update(['status' => 'processing']);
        return back()->with('success',"Request marked as processing.");
    }
    public function assignPC(Request $request, ServiceRequest $serviceRequest) {
        $this->guard();
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
        $this->guard();
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
        $this->guard();
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

    private function reduceStock(ServiceRequest $sr): void {
        if (in_array($sr->service_type, ['printing','photocopy']) && $sr->paper_size && $sr->copies) {
            \App\Models\InventoryItem::where('category','paper_size')
                ->where('value', $sr->paper_size)
                ->where('stock', '>', 0)
                ->decrement('stock', min($sr->copies, \App\Models\InventoryItem::where('category','paper_size')->where('value',$sr->paper_size)->value('stock') ?? 0));
        }
    }
}