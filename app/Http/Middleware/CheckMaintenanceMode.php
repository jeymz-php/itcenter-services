<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Setting;

class CheckMaintenanceMode
{
    // Admin routes always stay reachable — otherwise a super admin who turns
    // maintenance ON has no way to turn it back OFF except direct DB access.
    // Everything else (public site, login, register, dashboard, requests) is
    // blocked while maintenance is on.
    public function handle(Request $request, Closure $next)
    {
        $isMaintenance = Setting::get('system_maintenance_mode', '0') === '1';

        if ($isMaintenance && !$request->is('admin*')) {
            $message = Setting::get(
                'system_maintenance_message',
                'The system is currently undergoing scheduled maintenance. Please check back soon.'
            );

            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['message' => $message, 'maintenance' => true], 503);
            }

            return response()->view('maintenance', compact('message'), 503);
        }

        return $next($request);
    }
}