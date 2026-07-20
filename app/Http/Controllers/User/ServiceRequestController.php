<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Models\InventoryItem;
use App\Models\AdminNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class ServiceRequestController extends Controller
{
    public function downloadReport(ServiceRequest $serviceRequest) {
        if ($serviceRequest->user_id !== Auth::id()) abort(403);

        $r = $serviceRequest->load(['user','computer','computerSession','admin']);

        $pdf = Pdf::loadView('user.requests.pdf-report', compact('r'))
                   ->setPaper([0, 0, 226.77, $r->estimatedReceiptHeightPt()]);

        // Stream (not download) so it opens in a new tab for preview first —
        // the browser's own PDF viewer provides the Download button from there.
        return $pdf->stream("Receipt-{$r->request_number}.pdf");
    }

    public function printing() {
        $paperSizes = InventoryItem::paperSizes(Auth::user()->campus);
        $pageLimit      = ServiceRequest::dailyPrintingLimit();
        $pagesUsed      = ServiceRequest::printingPagesUsedToday(Auth::id());
        $pagesRemaining = ServiceRequest::printingPagesRemainingToday(Auth::id());
        return view('user.requests.printing', compact('paperSizes','pageLimit','pagesUsed','pagesRemaining'));
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

        $uploadedFile  = $request->file('file');
        $detectedPages = \App\Services\FilePageDetector::detect($uploadedFile);
        $copies        = (int) $request->copies;

        // Total sheets = pages × copies
        $totalSheets = $detectedPages ? ($detectedPages * $copies) : $copies;

        // Daily printing page limit — students/faculty only
        $pageLimit = ServiceRequest::dailyPrintingLimit();
        $remaining = ServiceRequest::printingPagesRemainingToday(Auth::id());
        if ($totalSheets > $remaining) {
            return back()->withErrors([
                'error' => $remaining > 0
                    ? "This request needs {$totalSheets} sheet(s), but you only have {$remaining} page(s) left of your daily {$pageLimit}-page printing limit. Resets at 12:00 AM."
                    : "You've reached your daily {$pageLimit}-page printing limit. It resets at 12:00 AM.",
            ])->withInput();
        }

        $filePath = $uploadedFile->store('service_files', 'public');
        $fileName = $uploadedFile->getClientOriginalName();

        $sr = ServiceRequest::create([
            'request_number' => ServiceRequest::generateNumber(),
            'user_id'        => Auth::id(),
            'service_type'   => 'printing',
            'paper_size'     => $request->paper_size,
            'copies'         => $copies,
            'print_type'     => $request->print_type,
            'purpose'        => $request->purpose,
            'file_path'      => $filePath,
            'file_name'      => $fileName,
            'detected_pages' => $detectedPages,
        ]);

        AdminNotification::notify(
            'new_print_request', 'New Printing Request',
            Auth::user()->full_name." submitted printing request ({$sr->request_number})."
            .($detectedPages ? " File has {$detectedPages} page(s) × {$copies} copies = {$totalSheets} sheet(s)." : ''),
            Auth::user(),
            route('admin.service-requests.index'),
            'fa-print'
        );

        $msg = "Printing request {$sr->request_number} submitted!";
        if ($detectedPages) {
            $msg .= " Detected {$detectedPages} page(s) × {$copies} copies = {$totalSheets} sheet(s) of "
                . strtoupper($request->paper_size) . " paper.";
        }

        return redirect()->route('dashboard')->with('success', $msg);
    }

    public function photocopy() {
        $paperSizes = InventoryItem::paperSizes(Auth::user()->campus);
        $pageLimit      = ServiceRequest::dailyPhotocopyLimit();
        $pagesUsed      = ServiceRequest::photocopyPagesUsedToday(Auth::id());
        $pagesRemaining = ServiceRequest::photocopyPagesRemainingToday(Auth::id());
        return view('user.requests.photocopy', compact('paperSizes','pageLimit','pagesUsed','pagesRemaining'));
    }

    public function storePhotocopy(Request $request) {
        $request->validate([
            'paper_size' => 'required|string',
            'copies'     => 'required|integer|min:1|max:100',
            'purpose'    => 'required|string|max:500',
            'terms'      => 'accepted',
        ]);

        // Daily photocopy page limit — students/faculty only
        $copies    = (int) $request->copies;
        $pageLimit = ServiceRequest::dailyPhotocopyLimit();
        $remaining = ServiceRequest::photocopyPagesRemainingToday(Auth::id());
        if ($copies > $remaining) {
            return back()->withErrors([
                'error' => $remaining > 0
                    ? "This request needs {$copies} page(s), but you only have {$remaining} page(s) left of your daily {$pageLimit}-page photocopy limit. Resets at 12:00 AM."
                    : "You've reached your daily {$pageLimit}-page photocopy limit. It resets at 12:00 AM.",
            ])->withInput();
        }

        $sr = ServiceRequest::create([
            'request_number' => ServiceRequest::generateNumber(),
            'user_id'        => Auth::id(),
            'service_type'   => 'photocopy',
            'paper_size'     => $request->paper_size,
            'copies'         => $copies,
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
        $durations = InventoryItem::pcDurations(Auth::user()->campus);
        $minutesLimit     = ServiceRequest::dailyResearchLimit();
        $minutesUsed      = ServiceRequest::minutesUsedToday(Auth::id());
        $minutesRemaining = ServiceRequest::minutesRemainingToday(Auth::id());
        return view('user.requests.research', compact('durations','minutesLimit','minutesUsed','minutesRemaining'));
    }

    public function storeResearch(Request $request) {
        $request->validate([
            'duration_minutes' => 'required|integer',
            'purpose'          => 'required|string|max:500',
            'terms'            => 'accepted',
        ]);

        // Daily research/PC-lab minute limit — students/faculty only
        $minutes      = (int) $request->duration_minutes;
        $minutesLimit = ServiceRequest::dailyResearchLimit();
        $remaining    = ServiceRequest::minutesRemainingToday(Auth::id());
        if ($minutes > $remaining) {
            return back()->withErrors([
                'error' => $remaining > 0
                    ? "This request needs {$minutes} minute(s), but you only have {$remaining} minute(s) left of your daily {$minutesLimit}-minute research limit. Resets at 12:00 AM."
                    : "You've reached your daily {$minutesLimit}-minute research/PC-lab limit. It resets at 12:00 AM.",
            ]);
        }

        $sr = ServiceRequest::create([
            'request_number'   => ServiceRequest::generateNumber(),
            'user_id'          => Auth::id(),
            'service_type'     => 'research',
            'duration_minutes' => $minutes,
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

    public function history(Request $request) {
        $query = ServiceRequest::where('user_id', Auth::id())
                            ->with(['computer','computerSession']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->latest()->paginate(10)->withQueryString();
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

    public function detectPages(Request $request) {
        $request->validate([
            'file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ]);

        $pages = \App\Services\FilePageDetector::detect($request->file('file'));

        return response()->json([
            'pages'   => $pages,
            'success' => $pages !== null,
        ]);
    }
}