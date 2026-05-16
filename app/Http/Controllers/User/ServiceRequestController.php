<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Models\InventoryItem;
use App\Models\AdminNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceRequestController extends Controller
{
    public function printing() {
        $paperSizes = InventoryItem::paperSizes();
        return view('user.requests.printing', compact('paperSizes'));
    }

    public function storePrinting(Request $request) {
        $request->validate([
            'paper_size' => 'required|string',
            'copies'     => 'required|integer|min:1|max:100',
            'print_type' => 'required|in:black_white,colored',
            'purpose'    => 'required|string|max:500',
            'file'       => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'terms'      => 'accepted',
        ]);

        $filePath = $request->file('file')->store('service_files', 'public');
        $fileName = $request->file('file')->getClientOriginalName();

        $sr = ServiceRequest::create([
            'request_number' => ServiceRequest::generateNumber(),
            'user_id'        => Auth::id(),
            'service_type'   => 'printing',
            'paper_size'     => $request->paper_size,
            'copies'         => $request->copies,
            'print_type'     => $request->print_type,
            'purpose'        => $request->purpose,
            'file_path'      => $filePath,
            'file_name'      => $fileName,
        ]);

        AdminNotification::notify(
            'new_print_request', 'New Printing Request',
            Auth::user()->full_name." submitted printing request ({$sr->request_number}).",
            Auth::user(), route('admin.service-requests.index'), 'fa-print'
        );

        return redirect()->route('dashboard')
               ->with('success', "Printing request {$sr->request_number} submitted!");
    }

    public function photocopy() {
        $paperSizes = InventoryItem::paperSizes();
        return view('user.requests.photocopy', compact('paperSizes'));
    }

    public function storePhotocopy(Request $request) {
        $request->validate([
            'paper_size' => 'required|string',
            'copies'     => 'required|integer|min:1|max:100',
            'purpose'    => 'required|string|max:500',
            'terms'      => 'accepted',
        ]);

        $sr = ServiceRequest::create([
            'request_number' => ServiceRequest::generateNumber(),
            'user_id'        => Auth::id(),
            'service_type'   => 'photocopy',
            'paper_size'     => $request->paper_size,
            'copies'         => $request->copies,
            'purpose'        => $request->purpose,
        ]);

        AdminNotification::notify(
            'new_photocopy_request', 'New Photocopy Request',
            Auth::user()->full_name." submitted photocopy request ({$sr->request_number}).",
            Auth::user(), route('admin.service-requests.index'), 'fa-copy'
        );

        return redirect()->route('dashboard')
               ->with('success', "Photocopy request {$sr->request_number} submitted!");
    }

    public function research() {
        $durations = InventoryItem::pcDurations();
        return view('user.requests.research', compact('durations'));
    }

    public function storeResearch(Request $request) {
        $request->validate([
            'duration_minutes' => 'required|integer',
            'purpose'          => 'required|string|max:500',
            'terms'            => 'accepted',
        ]);

        $sr = ServiceRequest::create([
            'request_number'   => ServiceRequest::generateNumber(),
            'user_id'          => Auth::id(),
            'service_type'     => 'research',
            'duration_minutes' => $request->duration_minutes,
            'purpose'          => $request->purpose,
        ]);

        AdminNotification::notify(
            'new_research_request', 'New Research Request',
            Auth::user()->full_name." submitted a computer research request ({$sr->request_number}).",
            Auth::user(), route('admin.service-requests.index'), 'fa-desktop'
        );

        return redirect()->route('dashboard')
               ->with('success', "Research request {$sr->request_number} submitted!");
    }

    public function history() {
        $requests = ServiceRequest::where('user_id', Auth::id())
                                  ->latest()->paginate(15);
        return view('user.requests.history', compact('requests'));
    }

    public function requestExtend(Request $request, \App\Models\ServiceRequest $serviceRequest) {
        $request->validate(['extend_minutes' => 'required|integer|in:15,30,45,60']);

        if ($serviceRequest->user_id !== Auth::id()) abort(403);

        $session = $serviceRequest->computerSession;
        if (!$session || !in_array($session->status, ['active','extended'])) {
            return back()->withErrors(['error' => 'No active session to extend.']);
        }

        // Create notification for admin
        \App\Models\AdminNotification::create([
            'type'            => 'extend_request',
            'title'           => 'Session Extension Request',
            'message'         => Auth::user()->full_name." requests a {$request->extend_minutes}-minute extension on {$session->computer->name} ({$serviceRequest->request_number}).",
            'notifiable_id'   => Auth::id(),
            'notifiable_type' => 'App\\Models\\User',
            'action_url'      => route('admin.service-requests.show', $serviceRequest),
            'icon'            => 'fa-clock',
        ]);

        return back()->with('success', "Extension request for {$request->extend_minutes} minutes submitted to admin.");
    }
}