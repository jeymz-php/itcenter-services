<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GuestRequest;
use Illuminate\Http\Request;

class GuestRequestController extends Controller
{
    private function guard() { if (!session('admin')) abort(403); }

    public function index(Request $request) {
        $this->guard();
        $query = GuestRequest::query();

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
        $counts = [
            'all'        => GuestRequest::count(),
            'pending'    => GuestRequest::where('status','pending')->count(),
            'approved'   => GuestRequest::where('status','approved')->count(),
            'processing' => GuestRequest::where('status','processing')->count(),
            'completed'  => GuestRequest::where('status','completed')->count(),
            'rejected'   => GuestRequest::where('status','rejected')->count(),
        ];
        return view('admin.guest-requests.index', compact('requests','counts'));
    }

    public function show(GuestRequest $guestRequest) {
        $this->guard();
        return view('admin.guest-requests.show', ['gr' => $guestRequest]);
    }

    public function approve(GuestRequest $guestRequest) {
        $this->guard();
        $guestRequest->update([
            'status'      => 'approved',
            'reviewed_by' => session('admin')->id,
            'reviewed_at' => now(),
        ]);
        return back()->with('success', "Guest request {$guestRequest->request_number} approved.");
    }

    public function reject(Request $request, GuestRequest $guestRequest) {
        $this->guard();
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
        $this->guard();
        $guestRequest->update(['status' => 'processing']);
        return back()->with('success', "Marked as processing.");
    }

    public function complete(GuestRequest $guestRequest) {
        $this->guard();
        $guestRequest->update(['status' => 'completed']);
        return back()->with('success', "Marked as completed.");
    }
}