<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rating;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    private function guard() { if (!session('admin')) abort(403); return session('admin'); }

    public function index(Request $request) {
        $admin = $this->guard();

        $query = Rating::with(['user', 'serviceRequest']);

        if ($admin->role !== 'super_admin') {
            $query->whereHas('user', fn($u) => $u->where('campus', $admin->campus));
        }

        if ($request->filled('campus') && $admin->role === 'super_admin') {
            $query->whereHas('user', fn($u) => $u->where('campus', $request->campus));
        }

        if ($request->filled('stars')) {
            $query->where('stars', $request->stars);
        }

        if ($request->filled('service_type')) {
            $query->whereHas('serviceRequest', fn($sr) => $sr->where('service_type', $request->service_type));
        }

        if ($request->filled('visibility')) {
            $query->where('is_anonymous', $request->visibility === 'anonymous');
        }

        $ratings = $query->latest()->paginate(15)->withQueryString();

        $statsBase = Rating::query();
        if ($admin->role !== 'super_admin') {
            $statsBase->whereHas('user', fn($u) => $u->where('campus', $admin->campus));
        } elseif ($request->filled('campus')) {
            $statsBase->whereHas('user', fn($u) => $u->where('campus', $request->campus));
        }
        $stats = [
            'total'     => (clone $statsBase)->count(),
            'average'   => round((clone $statsBase)->avg('stars') ?? 0, 1),
            'five_star' => (clone $statsBase)->where('stars', 5)->count(),
            'anonymous' => (clone $statsBase)->where('is_anonymous', true)->count(),
        ];

        return view('admin.review-ratings.index', compact('ratings', 'stats'));
    }
}