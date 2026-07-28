<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
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

        return view('admin.reports.index', compact('byService','byDay','byDayPaperUsage','byCampus','totals','from','to','campus'));
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