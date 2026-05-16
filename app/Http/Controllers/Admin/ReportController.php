<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\ComputerSession;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    private function guard() { if (!session('admin')) abort(403); }

    public function index(Request $request) {
        $this->guard();
        $from = $request->from ?? now()->startOfMonth()->toDateString();
        $to   = $request->to   ?? now()->toDateString();

        $byService = ServiceRequest::selectRaw('service_type, count(*) as total, sum(case when status="completed" then 1 else 0 end) as completed, sum(case when status="rejected" then 1 else 0 end) as rejected')
            ->whereBetween('created_at', [$from, $to.' 23:59:59'])
            ->groupBy('service_type')->get();

        $byDay = ServiceRequest::selectRaw('DATE(created_at) as date, count(*) as total')
            ->whereBetween('created_at', [$from, $to.' 23:59:59'])
            ->groupBy('date')->orderBy('date')->get();

        $byCampus = ServiceRequest::selectRaw('users.campus, count(*) as total')
            ->join('users','users.id','=','service_requests.user_id')
            ->whereBetween('service_requests.created_at', [$from, $to.' 23:59:59'])
            ->groupBy('users.campus')->get();

        $totals = [
            'requests'   => ServiceRequest::whereBetween('created_at',[$from,$to.' 23:59:59'])->count(),
            'completed'  => ServiceRequest::whereBetween('created_at',[$from,$to.' 23:59:59'])->where('status','completed')->count(),
            'pending'    => ServiceRequest::where('status','pending')->count(),
            'users'      => User::whereBetween('created_at',[$from,$to.' 23:59:59'])->count(),
            'pc_hours'   => round(ComputerSession::whereBetween('created_at',[$from,$to.' 23:59:59'])->where('status','completed')->sum('duration_minutes') / 60, 1),
        ];

        return view('admin.reports.index', compact('byService','byDay','byCampus','totals','from','to'));
    }
}