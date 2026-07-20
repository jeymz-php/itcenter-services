<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    private function superAdminGuard() {
        if (!session('admin') || session('admin')->role !== 'super_admin') abort(403);
    }

    public function index() {
        $this->superAdminGuard();

        $dailyPrintingLimit  = ServiceRequest::dailyPrintingLimit();
        $dailyPhotocopyLimit = ServiceRequest::dailyPhotocopyLimit();
        $dailyResearchLimit  = ServiceRequest::dailyResearchLimit();

        return view('admin.settings.index', compact('dailyPrintingLimit', 'dailyPhotocopyLimit', 'dailyResearchLimit'));
    }

    public function update(Request $request) {
        $this->superAdminGuard();

        $request->validate([
            'daily_printing_page_limit'  => 'required|integer|min:1|max:1000',
            'daily_photocopy_page_limit' => 'required|integer|min:1|max:1000',
            'daily_research_minutes'     => 'required|integer|min:1|max:1440',
        ]);

        Setting::set('daily_printing_page_limit', (int) $request->daily_printing_page_limit);
        Setting::set('daily_photocopy_page_limit', (int) $request->daily_photocopy_page_limit);
        Setting::set('daily_research_minutes', (int) $request->daily_research_minutes);

        return back()->with('success', 'Daily usage limits updated.');
    }
}