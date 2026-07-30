<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Models\GuestRequest;
use App\Models\User;
use App\Models\ComputerSession;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    private function guard() { if (!session('admin')) abort(403); return session('admin'); }

    public function index(Request $request) {
        $admin = $this->guard();
        $from = $request->from ?? now()->startOfMonth()->toDateString();
        $to   = $request->to   ?? now()->toDateString();

        $campus = $admin->role === 'super_admin' ? $request->campus : $admin->campus;

        $byService = ServiceRequest::selectRaw('service_type, count(*) as total, sum(case when status="completed" then 1 else 0 end) as completed, sum(case when status="rejected" then 1 else 0 end) as rejected')
            ->whereBetween('created_at', [$from, $to.' 23:59:59'])
            ->when($campus, fn($q) => $q->whereHas('user', fn($u) => $u->where('campus', $campus)))
            ->groupBy('service_type')->get();

        $byDay = ServiceRequest::selectRaw('DATE(created_at) as date, count(*) as total')
            ->whereBetween('created_at', [$from, $to.' 23:59:59'])
            ->when($campus, fn($q) => $q->whereHas('user', fn($u) => $u->where('campus', $campus)))
            ->groupBy('date')->orderBy('date')->get();

        $byDayPaperUsage = ServiceRequest::selectRaw("
                DATE(created_at) as date,
                SUM(CASE WHEN service_type = 'printing' THEN COALESCE(detected_pages, 1) * copies ELSE 0 END) as printing_sheets,
                SUM(CASE WHEN service_type = 'photocopy' THEN copies ELSE 0 END) as photocopy_sheets
            ")
            ->whereIn('service_type', ['printing', 'photocopy'])
            ->whereNotIn('status', ['rejected', 'cancelled'])
            ->whereBetween('created_at', [$from, $to.' 23:59:59'])
            ->when($campus, fn($q) => $q->whereHas('user', fn($u) => $u->where('campus', $campus)))
            ->groupBy('date')->orderBy('date')->get()
            ->map(function ($row) {
                $row->total_sheets = $row->printing_sheets + $row->photocopy_sheets;
                return $row;
            });

        $byCampus = ServiceRequest::selectRaw('users.campus, count(*) as total')
            ->join('users','users.id','=','service_requests.user_id')
            ->whereBetween('service_requests.created_at', [$from, $to.' 23:59:59'])
            ->when($campus, fn($q) => $q->where('users.campus', $campus))
            ->groupBy('users.campus')->get();

        $totals = [
            'requests'   => ServiceRequest::whereBetween('created_at',[$from,$to.' 23:59:59'])
                                ->when($campus, fn($q) => $q->whereHas('user', fn($u) => $u->where('campus', $campus)))
                                ->count(),
            'completed'  => ServiceRequest::whereBetween('created_at',[$from,$to.' 23:59:59'])->where('status','completed')
                                ->when($campus, fn($q) => $q->whereHas('user', fn($u) => $u->where('campus', $campus)))
                                ->count(),
            'pending'    => ServiceRequest::where('status','pending')
                                ->when($campus, fn($q) => $q->whereHas('user', fn($u) => $u->where('campus', $campus)))
                                ->count(),
            'users'      => User::whereBetween('created_at',[$from,$to.' 23:59:59'])
                                ->when($campus, fn($q) => $q->where('campus', $campus))
                                ->count(),
            'pc_hours'   => round(ComputerSession::whereBetween('created_at',[$from,$to.' 23:59:59'])->where('status','completed')
                                ->when($campus, fn($q) => $q->whereHas('user', fn($u) => $u->where('campus', $campus)))
                                ->sum('duration_minutes') / 60, 1),
        ];

        // ── USAGE BY CAMPUS FOR A GIVEN DAY ── defaults to today if no
        // date was picked, but can be pointed at any past day via the
        // usage_date filter — independent of the $from/$to range above,
        // since this section is always a single-day snapshot, not a range.
        // Combines logged-in students/faculty AND guests, since both
        // consume real paper and PC time and leaving guests out would
        // understate what a campus actually used that day.
        $usageDate = $request->filled('usage_date')
            ? \Carbon\Carbon::parse($request->usage_date)->startOfDay()
            : now()->startOfDay();
        $usageDateEnd = $usageDate->copy()->endOfDay();

        $todayFromUsers = ServiceRequest::selectRaw("
                users.campus as campus,
                SUM(CASE WHEN service_requests.service_type='printing' THEN COALESCE(service_requests.detected_pages,1)*service_requests.copies ELSE 0 END) as printing_sheets,
                SUM(CASE WHEN service_requests.service_type='photocopy' THEN service_requests.copies ELSE 0 END) as photocopy_sheets,
                SUM(CASE WHEN service_requests.service_type='research' THEN service_requests.duration_minutes ELSE 0 END) as research_minutes
            ")
            ->join('users','users.id','=','service_requests.user_id')
            ->whereIn('service_requests.service_type', ['printing','photocopy','research'])
            ->whereNotIn('service_requests.status', ['rejected','cancelled'])
            ->whereBetween('service_requests.created_at', [$usageDate, $usageDateEnd])
            ->when($campus, fn($q) => $q->where('users.campus', $campus))
            ->groupBy('users.campus')
            ->get()->keyBy('campus');

        $todayFromGuests = GuestRequest::selectRaw("
                campus,
                SUM(CASE WHEN service_type='printing' THEN COALESCE(detected_pages,1)*copies ELSE 0 END) as printing_sheets,
                SUM(CASE WHEN service_type='photocopy' THEN copies ELSE 0 END) as photocopy_sheets,
                SUM(CASE WHEN service_type='research' THEN duration_minutes ELSE 0 END) as research_minutes
            ")
            ->whereIn('service_type', ['printing','photocopy','research'])
            ->whereNotIn('status', ['rejected','cancelled'])
            ->whereBetween('created_at', [$usageDate, $usageDateEnd])
            ->when($campus, fn($q) => $q->where('campus', $campus))
            ->groupBy('campus')
            ->get()->keyBy('campus');

        $campusKeys = $admin->role === 'super_admin'
            ? ($campus ? [$campus] : array_keys(config('campuses')))
            : [$admin->campus];

        $todayByCampus = collect($campusKeys)->map(function ($c) use ($todayFromUsers, $todayFromGuests) {
            $u = $todayFromUsers->get($c);
            $g = $todayFromGuests->get($c);
            $printing  = ($u->printing_sheets ?? 0) + ($g->printing_sheets ?? 0);
            $photocopy = ($u->photocopy_sheets ?? 0) + ($g->photocopy_sheets ?? 0);
            $minutes   = ($u->research_minutes ?? 0) + ($g->research_minutes ?? 0);
            return (object) [
                'campus'           => $c,
                'campus_label'     => config('campuses.' . $c, $c),
                'printing_sheets'  => (int) $printing,
                'photocopy_sheets' => (int) $photocopy,
                'research_minutes' => (int) $minutes,
                'research_hours'   => floor($minutes / 60),
                'research_mins_rem'=> $minutes % 60,
            ];
        });

        $usageDateString = $usageDate->toDateString();

        return view('admin.reports.index', compact(
            'byService','byDay','byDayPaperUsage','byCampus','totals','from','to','campus','todayByCampus','usageDateString'
        ));
    }

    public function downloadPdf(Request $request) {
        $admin = $this->guard();
        $from = $request->from ?? now()->startOfMonth()->toDateString();
        $to   = $request->to   ?? now()->toDateString();
        $campus = $admin->role === 'super_admin' ? $request->campus : $admin->campus;

        $byService = ServiceRequest::selectRaw('service_type, count(*) as total, sum(case when status="completed" then 1 else 0 end) as completed, sum(case when status="rejected" then 1 else 0 end) as rejected')
            ->whereBetween('created_at', [$from, $to.' 23:59:59'])
            ->when($campus, fn($q) => $q->whereHas('user', fn($u) => $u->where('campus', $campus)))
            ->groupBy('service_type')->get();

        $byCampus = ServiceRequest::selectRaw('users.campus, count(*) as total')
            ->join('users','users.id','=','service_requests.user_id')
            ->whereBetween('service_requests.created_at', [$from, $to.' 23:59:59'])
            ->when($campus, fn($q) => $q->where('users.campus', $campus))
            ->groupBy('users.campus')->get();

        $totals = [
            'requests'   => ServiceRequest::whereBetween('created_at',[$from,$to.' 23:59:59'])
                                ->when($campus, fn($q) => $q->whereHas('user', fn($u) => $u->where('campus', $campus)))
                                ->count(),
            'completed'  => ServiceRequest::whereBetween('created_at',[$from,$to.' 23:59:59'])->where('status','completed')
                                ->when($campus, fn($q) => $q->whereHas('user', fn($u) => $u->where('campus', $campus)))
                                ->count(),
            'pending'    => ServiceRequest::where('status','pending')
                                ->when($campus, fn($q) => $q->whereHas('user', fn($u) => $u->where('campus', $campus)))
                                ->count(),
            'users'      => User::whereBetween('created_at',[$from,$to.' 23:59:59'])
                                ->when($campus, fn($q) => $q->where('campus', $campus))
                                ->count(),
            'pc_hours'   => round(ComputerSession::whereBetween('created_at',[$from,$to.' 23:59:59'])->where('status','completed')
                                ->when($campus, fn($q) => $q->whereHas('user', fn($u) => $u->where('campus', $campus)))
                                ->sum('duration_minutes') / 60, 1),
        ];

        $userBreakdown = User::query()
            ->when($campus, fn($q) => $q->where('campus', $campus))
            ->whereHas('serviceRequests', fn($q) => $q->whereBetween('created_at', [$from, $to.' 23:59:59']))
            ->withCount([
                'serviceRequests as printing_count' => fn($q) => $q->where('service_type','printing')->whereBetween('created_at',[$from,$to.' 23:59:59']),
                'serviceRequests as photocopy_count' => fn($q) => $q->where('service_type','photocopy')->whereBetween('created_at',[$from,$to.' 23:59:59']),
                'serviceRequests as research_count'  => fn($q) => $q->where('service_type','research')->whereBetween('created_at',[$from,$to.' 23:59:59']),
                'serviceRequests as completed_count' => fn($q) => $q->where('status','completed')->whereBetween('created_at',[$from,$to.' 23:59:59']),
                'serviceRequests as total_count'     => fn($q) => $q->whereBetween('created_at',[$from,$to.' 23:59:59']),
            ])
            ->orderByDesc('total_count')
            ->get();

        $generatedBy = $admin->admin_id;
        $campusLabel = $campus ? config('campuses.'.$campus, $campus) : 'All Campuses';

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.reports.pdf', compact(
            'byService','byCampus','totals','userBreakdown','from','to','campusLabel','generatedBy'
        ))->setPaper('a4','portrait');

        return $pdf->stream('IT-Center-Report-'.$from.'-to-'.$to.'.pdf');
    }
}