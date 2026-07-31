<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Models\InventoryItem;
use App\Models\AdminNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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


    /**
     * Display one of the authenticated user's service requests.
     *
     * AJAX requests receive only the modal body. A normal browser request
     * receives a complete details page, which also gives the View Details
     * link a usable fallback when JavaScript is unavailable.
     */
    public function show(Request $request, ServiceRequest $serviceRequest) {
        if ($serviceRequest->user_id !== Auth::id()) {
            abort(403);
        }

        $r = $serviceRequest->load(['computer', 'computerSession', 'admin']);

        if ($request->ajax() || $request->boolean('modal')) {
            return view('user.requests._details', compact('r'));
        }

        return view('user.requests.show', compact('r'));
    }

    /**
     * Open the uploaded printing file through an authenticated route instead
     * of exposing the storage path directly from the My Requests page.
     */
    public function openFile(ServiceRequest $serviceRequest) {
        if ($serviceRequest->user_id !== Auth::id()) {
            abort(403);
        }

        if ($serviceRequest->service_type !== 'printing' || !$serviceRequest->file_path) {
            abort(404);
        }

        $disk = Storage::disk('public');
        if (!$disk->exists($serviceRequest->file_path)) {
            abort(404, 'The uploaded file could not be found.');
        }

        return $disk->response(
            $serviceRequest->file_path,
            $serviceRequest->file_name ?: basename($serviceRequest->file_path)
        );
    }

    public function printing() {
        $serviceRequest = null;
        $paperSizes = InventoryItem::paperSizes(Auth::user()->campus);
        $pageLimit      = ServiceRequest::dailyPrintingLimit();
        $pagesUsed      = ServiceRequest::printingPagesUsedToday(Auth::id());
        $pagesRemaining = ServiceRequest::printingPagesRemainingToday(Auth::id());
        return view('user.requests.printing', compact('serviceRequest','paperSizes','pageLimit','pagesUsed','pagesRemaining'));
    }

    public function storePrinting(Request $request) {
        if (!\App\Models\Setting::isWithinSystemHours()) {
            return back()->withErrors(['error' => 'The IT Center is currently closed. Requests can only be submitted between ' . \App\Models\Setting::systemHoursLabel() . '.'])->withInput();
        }

        $request->validate([
            'paper_size' => 'required|string',
            'copies'     => 'required|integer|min:1|max:100',
            'print_type' => 'required|in:black_white,colored',
            'purpose'    => 'required|string|max:500',
            'file'       => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'terms'      => 'accepted',
            'submission_confirmed' => 'accepted',
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

    public function editPrinting(ServiceRequest $serviceRequest) {
        if ($serviceRequest->user_id !== Auth::id()) {
            abort(403);
        }

        if ($serviceRequest->service_type !== 'printing') {
            abort(404);
        }

        if ($serviceRequest->status !== 'pending') {
            return redirect()->route('requests.history')->withErrors([
                'error' => 'Only pending printing requests can be edited. This request is already ' . $serviceRequest->status . '.',
            ]);
        }

        $paperSizes = InventoryItem::paperSizes(Auth::user()->campus);
        $pageLimit      = ServiceRequest::dailyPrintingLimit();
        $pagesUsed      = ServiceRequest::printingPagesUsedToday(Auth::id(), $serviceRequest->id);
        $pagesRemaining = ServiceRequest::printingPagesRemainingToday(Auth::id(), $serviceRequest->id);

        return view('user.requests.printing', compact('serviceRequest','paperSizes','pageLimit','pagesUsed','pagesRemaining'));
    }

    public function updatePrinting(Request $request, ServiceRequest $serviceRequest) {
        if ($serviceRequest->user_id !== Auth::id()) {
            abort(403);
        }

        if ($serviceRequest->service_type !== 'printing') {
            abort(404);
        }

        if ($serviceRequest->status !== 'pending') {
            return redirect()->route('requests.history')->withErrors([
                'error' => 'This printing request can no longer be edited because its status is ' . $serviceRequest->status . '.',
            ]);
        }

        $request->validate([
            'paper_size' => 'required|string',
            'copies'     => 'required|integer|min:1|max:100',
            'print_type' => 'required|in:black_white,colored',
            'purpose'    => 'required|string|max:500',
            'file'       => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'terms'      => 'accepted',
            'submission_confirmed' => 'accepted',
        ]);

        $uploadedFile  = $request->file('file');
        $detectedPages = $uploadedFile
            ? \App\Services\FilePageDetector::detect($uploadedFile)
            : $serviceRequest->detected_pages;
        $copies        = (int) $request->copies;
        $totalSheets   = ((int) ($detectedPages ?: 1)) * $copies;

        // Exclude the request being edited so its previous sheet count is not
        // counted twice against the user's daily printing limit.
        $pageLimit = ServiceRequest::dailyPrintingLimit();
        $available = ServiceRequest::printingPagesRemainingToday(Auth::id(), $serviceRequest->id);

        if ($totalSheets > $available) {
            return back()->withErrors([
                'error' => $available > 0
                    ? "The updated request needs {$totalSheets} sheet(s), but only {$available} page(s) are available within your daily {$pageLimit}-page printing limit."
                    : "You've reached your daily {$pageLimit}-page printing limit. It resets at 12:00 AM.",
            ])->withInput();
        }

        $oldFilePath = $serviceRequest->file_path;
        $newFilePath = null;

        if ($uploadedFile) {
            $newFilePath = $uploadedFile->store('service_files', 'public');
        }

        $updateData = [
            'paper_size'     => $request->paper_size,
            'copies'         => $copies,
            'print_type'     => $request->print_type,
            'purpose'        => $request->purpose,
            'file_path'      => $uploadedFile ? $newFilePath : $serviceRequest->file_path,
            'file_name'      => $uploadedFile ? $uploadedFile->getClientOriginalName() : $serviceRequest->file_name,
            'detected_pages' => $detectedPages,
        ];

        try {
            // Keep this as a conditional database update. If an administrator
            // changes the request from pending while the user has the edit form
            // open, the update affects zero rows and the request stays locked.
            $updated = ServiceRequest::query()
                ->whereKey($serviceRequest->id)
                ->where('user_id', Auth::id())
                ->where('service_type', 'printing')
                ->where('status', 'pending')
                ->update($updateData);
        } catch (\Throwable $e) {
            if ($newFilePath) {
                Storage::disk('public')->delete($newFilePath);
            }
            throw $e;
        }

        if ($updated !== 1) {
            if ($newFilePath) {
                Storage::disk('public')->delete($newFilePath);
            }

            return redirect()->route('requests.history')->withErrors([
                'error' => 'This printing request can no longer be edited because its status changed from pending.',
            ]);
        }

        if ($uploadedFile && $oldFilePath && $oldFilePath !== $newFilePath) {
            Storage::disk('public')->delete($oldFilePath);
        }

        $serviceRequest->refresh();

        AdminNotification::notify(
            'updated_print_request', 'Printing Request Updated',
            Auth::user()->full_name." updated pending printing request ({$serviceRequest->request_number})."
            .($uploadedFile ? " The uploaded file was replaced with {$serviceRequest->file_name}." : ''),
            Auth::user(),
            route('admin.service-requests.show', $serviceRequest),
            'fa-pen-to-square'
        );

        return redirect()->route('requests.history')
            ->with('success', "Printing request {$serviceRequest->request_number} updated successfully.");
    }

    public function photocopy() {
        $paperSizes = InventoryItem::paperSizes(Auth::user()->campus);
        $pageLimit      = ServiceRequest::dailyPhotocopyLimit();
        $pagesUsed      = ServiceRequest::photocopyPagesUsedToday(Auth::id());
        $pagesRemaining = ServiceRequest::photocopyPagesRemainingToday(Auth::id());
        return view('user.requests.photocopy', compact('paperSizes','pageLimit','pagesUsed','pagesRemaining'));
    }

    public function storePhotocopy(Request $request) {
        if (!\App\Models\Setting::isWithinSystemHours()) {
            return back()->withErrors(['error' => 'The IT Center is currently closed. Requests can only be submitted between ' . \App\Models\Setting::systemHoursLabel() . '.'])->withInput();
        }

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
        $researchRestricted = Auth::user()->research_restricted;
        return view('user.requests.research', compact('durations','minutesLimit','minutesUsed','minutesRemaining','researchRestricted'));
    }

    public function storeResearch(Request $request) {
        if (Auth::user()->research_restricted) {
            return back()->withErrors(['error' => 'Your access to Research/PC-Lab requests has been restricted by an IT Center administrator. Contact the IT Center for details.']);
        }
        if (!\App\Models\Setting::isWithinSystemHours()) {
            return back()->withErrors(['error' => 'The IT Center is currently closed. Requests can only be submitted between ' . \App\Models\Setting::systemHoursLabel() . '.']);
        }

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