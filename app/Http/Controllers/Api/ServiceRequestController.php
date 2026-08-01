<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Models\InventoryItem;
use App\Models\AdminNotification;
use App\Models\Setting;
use App\Models\ComputerSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceRequestController extends Controller
{
    // ── DASHBOARD ──
    public function dashboard() {
        $user = Auth::user();

        $activeSession = ComputerSession::where('user_id', $user->id)
                         ->whereIn('status', ['active', 'extended'])
                         ->with('computer', 'serviceRequest')
                         ->first();

        return response()->json([
            'usage' => [
                'printing' => [
                    'available' => Setting::isServiceAvailable('printing'),
                    'limit'     => ServiceRequest::dailyPrintingLimit(),
                    'used'      => ServiceRequest::printingPagesUsedToday($user->id),
                    'remaining' => ServiceRequest::printingPagesRemainingToday($user->id),
                ],
                'photocopy' => [
                    'available' => Setting::isServiceAvailable('photocopy'),
                    'limit'     => ServiceRequest::dailyPhotocopyLimit(),
                    'used'      => ServiceRequest::photocopyPagesUsedToday($user->id),
                    'remaining' => ServiceRequest::photocopyPagesRemainingToday($user->id),
                ],
                'research' => [
                    'available'  => Setting::isServiceAvailable('research'),
                    'limit'      => ServiceRequest::dailyResearchLimit(),
                    'used'       => ServiceRequest::minutesUsedToday($user->id),
                    'remaining'  => ServiceRequest::minutesRemainingToday($user->id),
                    'restricted' => (bool) $user->research_restricted,
                ],
            ],
            'system' => [
                'open_now'    => Setting::isWithinSystemHours(),
                'hours_label' => Setting::todayHoursLabel(),
                'today'       => Setting::todaySchedule(),
                'schedule'    => Setting::operatingSchedule(),
                'services'    => Setting::serviceAvailability(),
            ],
            'active_session' => $activeSession ? [
                'request_number'   => $activeSession->serviceRequest->request_number,
                'computer_name'    => $activeSession->computer->name,
                'status'           => $activeSession->status,
                'started_at'       => $activeSession->started_at,
                'ends_at'          => $activeSession->ends_at,
                'remaining_seconds'=> $activeSession->remaining_seconds,
                'duration_minutes' => $activeSession->duration_minutes,
                'extended_minutes' => $activeSession->extended_minutes,
                'total_minutes'    => $activeSession->total_minutes,
            ] : null,
            'recent_requests' => ServiceRequest::where('user_id', $user->id)
                ->latest()->take(5)->get()
                ->map(fn ($r) => $this->requestPayload($r)),
        ]);
    }

    // ── PRINTING ──
    public function printingOptions() {
        $user = Auth::user();
        return response()->json([
            'available'   => Setting::isServiceAvailable('printing'),
            'paper_sizes' => InventoryItem::paperSizes($user->campus),
            'limit'       => ServiceRequest::dailyPrintingLimit(),
            'used'        => ServiceRequest::printingPagesUsedToday($user->id),
            'remaining'   => ServiceRequest::printingPagesRemainingToday($user->id),
        ]);
    }

    public function storePrinting(Request $request) {
        if (!Setting::isServiceAvailable('printing')) {
            return response()->json(['message' => Setting::serviceUnavailableMessage('printing')], 422);
        }
        if (!Setting::isWithinSystemHours()) {
            return response()->json(['message' => Setting::closedMessage(), 'today' => Setting::todaySchedule()], 422);
        }

        $request->validate([
            'paper_size' => 'required|string',
            'copies'     => 'required|integer|min:1|max:100',
            'print_type' => 'required|in:black_white,colored',
            'purpose'    => 'required|string|max:500',
            'file'       => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ]);

        $uploadedFile  = $request->file('file');
        $detectedPages = \App\Services\FilePageDetector::detect($uploadedFile);
        $copies        = (int) $request->copies;
        $totalSheets   = $detectedPages ? ($detectedPages * $copies) : $copies;

        $pageLimit = ServiceRequest::dailyPrintingLimit();
        $remaining = ServiceRequest::printingPagesRemainingToday(Auth::id());
        if ($totalSheets > $remaining) {
            return response()->json([
                'message' => $remaining > 0
                    ? "This request needs {$totalSheets} sheet(s), but you only have {$remaining} page(s) left of your daily {$pageLimit}-page printing limit. Resets at 12:00 AM."
                    : "You've reached your daily {$pageLimit}-page printing limit. It resets at 12:00 AM.",
            ], 422);
        }

        $filePath = $uploadedFile->store('service_files', 'public');

        $sr = ServiceRequest::create([
            'request_number' => ServiceRequest::generateNumber(),
            'user_id'        => Auth::id(),
            'service_type'   => 'printing',
            'paper_size'     => $request->paper_size,
            'copies'         => $copies,
            'print_type'     => $request->print_type,
            'purpose'        => $request->purpose,
            'file_path'      => $filePath,
            'file_name'      => $uploadedFile->getClientOriginalName(),
            'detected_pages' => $detectedPages,
        ]);

        AdminNotification::notify(
            'new_print_request', 'New Printing Request',
            Auth::user()->full_name . " submitted printing request ({$sr->request_number})."
            . ($detectedPages ? " File has {$detectedPages} page(s) × {$copies} copies = {$totalSheets} sheet(s)." : ''),
            Auth::user(), route('admin.service-requests.index'), 'fa-print'
        );

        return response()->json(['message' => "Printing request {$sr->request_number} submitted!", 'request' => $this->requestPayload($sr)], 201);
    }

    // ── PHOTOCOPY ──
    public function photocopyOptions() {
        $user = Auth::user();
        return response()->json([
            'available'   => Setting::isServiceAvailable('photocopy'),
            'paper_sizes' => InventoryItem::paperSizes($user->campus),
            'limit'       => ServiceRequest::dailyPhotocopyLimit(),
            'used'        => ServiceRequest::photocopyPagesUsedToday($user->id),
            'remaining'   => ServiceRequest::photocopyPagesRemainingToday($user->id),
        ]);
    }

    public function storePhotocopy(Request $request) {
        if (!Setting::isServiceAvailable('photocopy')) {
            return response()->json(['message' => Setting::serviceUnavailableMessage('photocopy')], 422);
        }
        if (!Setting::isWithinSystemHours()) {
            return response()->json(['message' => Setting::closedMessage(), 'today' => Setting::todaySchedule()], 422);
        }

        $request->validate([
            'paper_size' => 'required|string',
            'copies'     => 'required|integer|min:1|max:100',
            'purpose'    => 'required|string|max:500',
        ]);

        $copies    = (int) $request->copies;
        $pageLimit = ServiceRequest::dailyPhotocopyLimit();
        $remaining = ServiceRequest::photocopyPagesRemainingToday(Auth::id());
        if ($copies > $remaining) {
            return response()->json([
                'message' => $remaining > 0
                    ? "This request needs {$copies} page(s), but you only have {$remaining} page(s) left of your daily {$pageLimit}-page photocopy limit. Resets at 12:00 AM."
                    : "You've reached your daily {$pageLimit}-page photocopy limit. It resets at 12:00 AM.",
            ], 422);
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
            Auth::user()->full_name . " submitted photocopy request ({$sr->request_number}).",
            Auth::user(), route('admin.service-requests.index'), 'fa-copy'
        );

        return response()->json(['message' => "Photocopy request {$sr->request_number} submitted!", 'request' => $this->requestPayload($sr)], 201);
    }

    // ── RESEARCH ──
    public function researchOptions() {
        $user = Auth::user();
        return response()->json([
            'available'  => Setting::isServiceAvailable('research'),
            'durations'  => InventoryItem::pcDurations($user->campus),
            'limit'      => ServiceRequest::dailyResearchLimit(),
            'used'       => ServiceRequest::minutesUsedToday($user->id),
            'remaining'  => ServiceRequest::minutesRemainingToday($user->id),
            'restricted' => (bool) $user->research_restricted,
        ]);
    }

    public function storeResearch(Request $request) {
        if (!Setting::isServiceAvailable('research')) {
            return response()->json(['message' => Setting::serviceUnavailableMessage('research')], 422);
        }
        if (Auth::user()->research_restricted) {
            return response()->json(['message' => 'Your access to Research/PC-Lab requests has been restricted by an IT Center administrator. Contact the IT Center for details.'], 403);
        }
        if (!Setting::isWithinSystemHours()) {
            return response()->json(['message' => Setting::closedMessage(), 'today' => Setting::todaySchedule()], 422);
        }

        $request->validate([
            'duration_minutes' => 'required|integer',
            'purpose'          => 'required|string|max:500',
        ]);

        $minutes      = (int) $request->duration_minutes;
        $minutesLimit = ServiceRequest::dailyResearchLimit();
        $remaining    = ServiceRequest::minutesRemainingToday(Auth::id());
        if ($minutes > $remaining) {
            return response()->json([
                'message' => $remaining > 0
                    ? "This request needs {$minutes} minute(s), but you only have {$remaining} minute(s) left of your daily {$minutesLimit}-minute research limit. Resets at 12:00 AM."
                    : "You've reached your daily {$minutesLimit}-minute research/PC-lab limit. It resets at 12:00 AM.",
            ], 422);
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
            Auth::user()->full_name . " submitted a computer research request ({$sr->request_number}).",
            Auth::user(), route('admin.service-requests.index'), 'fa-desktop'
        );

        return response()->json(['message' => "Research request {$sr->request_number} submitted!", 'request' => $this->requestPayload($sr)], 201);
    }

    // ── HISTORY ──
    public function history(Request $request) {
        $query = ServiceRequest::where('user_id', Auth::id())->with(['computer', 'computerSession']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $paginated = $query->latest()->paginate(10);

        return response()->json([
            'data'         => $paginated->getCollection()->map(fn ($r) => $this->requestPayload($r)),
            'current_page' => $paginated->currentPage(),
            'last_page'    => $paginated->lastPage(),
            'total'        => $paginated->total(),
        ]);
    }

    public function requestExtend(Request $request, ServiceRequest $serviceRequest) {
        $request->validate(['extend_minutes' => 'required|integer|in:15,30,45,60']);

        if ($serviceRequest->user_id !== Auth::id()) abort(403);

        $session = $serviceRequest->computerSession;
        if (!$session || !in_array($session->status, ['active', 'extended'])) {
            return response()->json(['message' => 'No active session to extend.'], 422);
        }

        AdminNotification::create([
            'type'            => 'extend_request',
            'title'           => 'Session Extension Request',
            'message'         => Auth::user()->full_name . " requests a {$request->extend_minutes}-minute extension on {$session->computer->name} ({$serviceRequest->request_number}).",
            'notifiable_id'   => Auth::id(),
            'notifiable_type' => 'App\\Models\\User',
            'action_url'      => route('admin.service-requests.show', $serviceRequest),
            'icon'            => 'fa-clock',
        ]);

        return response()->json(['message' => "Extension request for {$request->extend_minutes} minutes submitted to admin."]);
    }

    public function detectPages(Request $request) {
        if (!Setting::isServiceAvailable('printing')) {
            return response()->json(['message' => Setting::serviceUnavailableMessage('printing')], 422);
        }

        $request->validate([
            'file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ]);

        $pages = \App\Services\FilePageDetector::detect($request->file('file'));

        return response()->json(['pages' => $pages, 'success' => $pages !== null]);
    }

    // Streams the exact same PDF the web app generates — same Blade view,
    // same dynamic receipt height — just token-authenticated instead of
    // session-authenticated, and streamed as raw bytes for the app to save
    // and open locally rather than rendered inline in a browser tab.
    public function downloadReceipt(ServiceRequest $serviceRequest) {
        if ($serviceRequest->user_id !== Auth::id()) abort(403);

        $r = $serviceRequest->load(['user', 'computer', 'computerSession', 'admin']);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('user.requests.pdf-report', compact('r'))
                   ->setPaper([0, 0, 226.77, $r->estimatedReceiptHeightPt()]);

        return $pdf->stream("Receipt-{$r->request_number}.pdf");
    }

    private function requestPayload(ServiceRequest $r): array {
        return [
            'id'               => $r->id,
            'request_number'   => $r->request_number,
            'service_type'     => $r->service_type,
            'status'           => $r->status,
            'paper_size'       => $r->paper_size,
            'copies'           => $r->copies,
            'print_type'       => $r->print_type,
            'detected_pages'   => $r->detected_pages,
            'duration_minutes' => $r->duration_minutes,
            'purpose'          => $r->purpose,
            'admin_note'       => $r->admin_note,
            'created_at'       => $r->created_at,
            'reviewed_at'      => $r->reviewed_at,
        ];
    }
}