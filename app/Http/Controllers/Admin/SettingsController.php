<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    private function adminGuard() {
        if (!session('admin')) abort(403);
    }

    private function superAdminGuard() {
        $this->adminGuard();
        if (session('admin')->role !== 'super_admin') abort(403);
    }

    public function index() {
        $this->adminGuard();

        $admin = session('admin');
        $isSuperAdmin = $admin->role === 'super_admin';

        $dailyPrintingLimit  = ServiceRequest::dailyPrintingLimit();
        $dailyPhotocopyLimit = ServiceRequest::dailyPhotocopyLimit();
        $dailyResearchLimit  = ServiceRequest::dailyResearchLimit();

        $systemOpenTime  = Setting::get('system_open_time', '07:00');
        $systemCloseTime = Setting::get('system_close_time', '21:30');

        $systemVersion      = Setting::get('system_version', '1.0.0');
        $systemVersionNotes = Setting::get('system_version_notes', '');
        $systemVersionUpdatedAt = Setting::get('system_version_updated_at');

        $versionHistory = collect(Setting::versionHistory());
        if (!$versionHistory->contains(fn ($entry) => ($entry['version'] ?? null) === $systemVersion)) {
            $versionHistory->prepend([
                'version'     => $systemVersion,
                'notes'       => $systemVersionNotes,
                'released_at' => $systemVersionUpdatedAt,
                'updated_at'  => $systemVersionUpdatedAt,
                'updated_by'  => null,
            ]);
        }

        $maintenanceMode    = Setting::get('system_maintenance_mode', '0') === '1';
        $maintenanceMessage = Setting::get('system_maintenance_message', 'The system is currently undergoing scheduled maintenance. Please check back soon.');

        $serviceAvailability = Setting::serviceAvailability();

        return view('admin.settings.index', compact(
            'admin', 'isSuperAdmin',
            'dailyPrintingLimit', 'dailyPhotocopyLimit', 'dailyResearchLimit',
            'systemOpenTime', 'systemCloseTime',
            'systemVersion', 'systemVersionNotes', 'versionHistory',
            'maintenanceMode', 'maintenanceMessage',
            'serviceAvailability'
        ));
    }

    public function updateServices(Request $request) {
        $this->adminGuard();

        DB::transaction(function () use ($request) {
            foreach (['printing', 'photocopy', 'research'] as $service) {
                Setting::setServiceAvailability($service, $request->boolean("service_{$service}_enabled"));
            }
        });

        $available = collect(Setting::serviceAvailability())
            ->filter()
            ->keys()
            ->map(fn ($service) => Setting::serviceLabel($service));

        $unavailable = collect(Setting::serviceAvailability())
            ->reject()
            ->keys()
            ->map(fn ($service) => Setting::serviceLabel($service));

        $message = 'Service availability updated.';
        if ($available->isNotEmpty()) {
            $message .= ' Available: ' . $available->join(', ') . '.';
        }
        if ($unavailable->isNotEmpty()) {
            $message .= ' Unavailable: ' . $unavailable->join(', ') . '.';
        }

        return back()->with('success', $message);
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

    public function updateHours(Request $request) {
        $this->superAdminGuard();

        $request->validate([
            'system_open_time'  => 'required|date_format:H:i',
            'system_close_time' => 'required|date_format:H:i',
        ]);

        Setting::set('system_open_time', $request->system_open_time);
        Setting::set('system_close_time', $request->system_close_time);

        return back()->with('success', 'System hours updated.');
    }

    public function updateVersion(Request $request) {
        $this->superAdminGuard();

        $request->validate([
            'system_version'       => 'required|string|max:30',
            'system_version_notes' => 'nullable|string|max:2000',
        ]);

        $previousVersion = Setting::get('system_version', '1.0.0');
        $previousNotes = Setting::get('system_version_notes', '');
        $previousReleasedAt = Setting::get('system_version_updated_at') ?: now()->toIso8601String();
        $adminIdentifier = session('admin')->admin_id ?? session('admin')->email ?? 'Administrator';

        $newVersion = trim($request->system_version);
        $newNotes = trim((string) ($request->system_version_notes ?? ''));
        $releasedAt = $previousVersion === $newVersion
            ? $previousReleasedAt
            : now()->toIso8601String();

        DB::transaction(function () use (
            $previousVersion, $previousNotes, $previousReleasedAt,
            $newVersion, $newNotes, $releasedAt, $adminIdentifier
        ) {
            // Preserve the current version before replacing it so it becomes
            // part of the permanent previous-version history.
            Setting::saveVersionLog(
                $previousVersion,
                $previousNotes,
                $previousReleasedAt,
                $adminIdentifier
            );

            Setting::set('system_version', $newVersion);
            Setting::set('system_version_notes', $newNotes);
            Setting::set('system_version_updated_at', $releasedAt);
            Setting::saveVersionLog($newVersion, $newNotes, $releasedAt, $adminIdentifier);
        });

        return back()->with('success',
            $previousVersion !== $newVersion
                ? "Version updated to {$newVersion}. Users will see the update notice next time they open the user portal."
                : 'Version notes and update history were updated.'
        );
    }

    public function updateMaintenance(Request $request) {
        $this->superAdminGuard();

        $request->validate([
            'system_maintenance_mode'    => 'nullable|boolean',
            'system_maintenance_message' => 'nullable|string|max:1000',
        ]);

        $enabled = $request->boolean('system_maintenance_mode');

        Setting::set('system_maintenance_mode', $enabled ? '1' : '0');
        if ($request->filled('system_maintenance_message')) {
            Setting::set('system_maintenance_message', $request->system_maintenance_message);
        }

        return back()->with('success', $enabled
            ? 'Maintenance mode is now ON — students/faculty cannot access the system. Admin access remains open.'
            : 'Maintenance mode is now OFF — the system is accessible to everyone again.'
        );
    }
}
