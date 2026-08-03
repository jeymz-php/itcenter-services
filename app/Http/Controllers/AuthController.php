<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ComputerSession;
use App\Models\ServiceRequest;
use App\Models\Setting;
use App\Mail\AccountPendingMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function showLogin()  {
        $publicRatings = \App\Models\Rating::with(['user', 'serviceRequest'])
            ->whereNotNull('comment')
            ->where('comment', '!=', '')
            ->inRandomOrder()
            ->limit(5)
            ->get();

        return view('auth.login', compact('publicRatings'));
    }
    public function showRegister(){ return view('auth.register'); }

    public function login(Request $request) {
        $request->validate([
            'campus'    => 'required|string',
            'user_type' => 'required|string',
            'id_number' => 'required|string',
            'password'  => 'required|string',
        ]);

        $user = \App\Models\User::where('id_number',  $request->id_number)
                                ->where('campus',     $request->campus)
                                ->where('user_type',  $request->user_type)
                                ->first();

        if (!$user || !\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
            return back()->withErrors(['login' => 'Invalid credentials. Please try again.'])->withInput();
        }

        if ($user->status === 'archived') {
            return back()->withErrors(['login' => 'This account has been archived and cannot be accessed. Please contact the IT Center.'])->withInput();
        }
        if ($user->status === 'rejected') {
            return back()->withErrors(['login' => 'Your account registration was rejected. Please contact the IT Center.'])->withInput();
        }

        \Illuminate\Support\Facades\Auth::login($user);
        return redirect()->route('dashboard');
    }

    public function register(Request $request) {
        $campuses   = array_keys(config('campuses'));
        $userTypes  = ['student','faculty_staff'];

        $request->validate([
            'id_number'        => [
                'required', 'string', 'size:8',
                Rule::unique('users')->where(fn ($q) => $q->where('campus', $request->campus)),
            ],
            'first_name'       => 'required|string|max:100',
            'last_name'        => 'required|string|max:100',
            'email'            => 'required|email|unique:users',
            'campus'           => 'required|in:'.implode(',',$campuses),
            'user_type'        => 'required|in:'.implode(',',$userTypes),
            'password'         => [
                'required','string','min:8','confirmed',
                'regex:/[A-Z]/','regex:/[0-9]/',
            ],
            'profile_picture' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'terms'            => 'accepted',
        ]);

        $picPath = null;
        if ($request->hasFile('profile_picture')) {
            $picPath = $request->file('profile_picture')->store('profile_pictures','public');
        }

        $user = User::create([
            'id_number'       => $request->id_number,
            'first_name'      => $request->first_name,
            'last_name'       => $request->last_name,
            'email'           => $request->email,
            'profile_picture' => $picPath,
            'campus'          => $request->campus,
            'user_type'       => $request->user_type,
            'password'        => Hash::make($request->password),
            'status'          => 'pending',
        ]);

        $emailWarning = null;
        try {
            Mail::to($user->email)->send(new AccountPendingMail($user, route('dashboard')));
        } catch (\Throwable $e) {
            Log::warning('Pending-account email could not be sent.', [
                'user_id' => $user->id,
                'email'   => $user->email,
                'error'   => $e->getMessage(),
            ]);
            $emailWarning = 'Your account was created, but the pending-approval email could not be delivered. You can still check your status from this dashboard.';
        }

        Auth::login($user);
        $redirect = redirect()->route('dashboard');
        if ($emailWarning) {
            $redirect->with('warning', $emailWarning);
        }
        return $redirect;
    }

    public function logout(Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    public function dashboard(\App\Services\ComputerSessionMonitor $monitor) {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $pendingRating = null;
        $activeSession = null;
        $recentRequests = collect();
        $stats = [
            'pending' => 0,
            'approved' => 0,
            'processing' => 0,
            'completed' => 0,
            'total' => 0,
        ];

        $printingLimit = $printingUsedToday = $printingRemainingToday = 0;
        $photocopyLimit = $photocopyUsedToday = $photocopyRemainingToday = 0;
        $minutesLimit = $minutesUsedToday = $minutesRemainingToday = 0;
        $serviceAvailability = Setting::serviceAvailability();
        $unavailableServices = collect($serviceAvailability)
            ->reject()
            ->keys()
            ->map(fn ($service) => Setting::serviceLabel($service));
        $systemOpenNow = Setting::isWithinSystemHours();
        $todayHours = Setting::todayHoursLabel();

        // Pending/deactivated/rejected dashboards do not need request-history,
        // usage, or active-session queries. This keeps their page load light.
        if ($user->status === 'active') {
            // Finalize any PC session whose occupied time already ran out before
            // loading dashboard statistics or the active-session banner.
            $monitor->expireDueSessionsForUser($user);

            $statusCounts = ServiceRequest::where('user_id', $user->id)
                ->selectRaw('status, COUNT(*) as aggregate')
                ->groupBy('status')
                ->pluck('aggregate', 'status');

            foreach (['pending', 'approved', 'processing', 'completed'] as $status) {
                $stats[$status] = (int) ($statusCounts[$status] ?? 0);
            }
            $stats['total'] = (int) $statusCounts->sum();

            $activeSession = ComputerSession::where('user_id', $user->id)
                ->whereIn('status', ['active', 'extended'])
                ->with(['computer', 'serviceRequest'])
                ->first();

            $recentRequests = ServiceRequest::where('user_id', $user->id)
                ->latest()
                ->take(5)
                ->get();

            $printingLimit = ServiceRequest::dailyPrintingLimit();
            $printingUsedToday = ServiceRequest::printingPagesUsedToday($user->id);
            $printingRemainingToday = max(0, $printingLimit - $printingUsedToday);

            $photocopyLimit = ServiceRequest::dailyPhotocopyLimit();
            $photocopyUsedToday = ServiceRequest::photocopyPagesUsedToday($user->id);
            $photocopyRemainingToday = max(0, $photocopyLimit - $photocopyUsedToday);

            $minutesLimit = ServiceRequest::dailyResearchLimit();
            $minutesUsedToday = ServiceRequest::minutesUsedToday($user->id);
            $minutesRemainingToday = max(0, $minutesLimit - $minutesUsedToday);

            $pendingRating = ServiceRequest::where('user_id', $user->id)
                ->where('status', 'completed')
                ->whereDoesntHave('rating')
                ->whereNull('rating_prompted_at')
                ->oldest('updated_at')
                ->first();

            // Reserve the prompt before rendering. Refreshing or revisiting the
            // dashboard will therefore not show the same review prompt again.
            if ($pendingRating) {
                $pendingRating->forceFill(['rating_prompted_at' => now()])->save();
            }
        }

        return view('dashboard', compact(
            'user', 'pendingRating', 'activeSession', 'recentRequests', 'stats',
            'printingLimit', 'printingUsedToday', 'printingRemainingToday',
            'photocopyLimit', 'photocopyUsedToday', 'photocopyRemainingToday',
            'minutesLimit', 'minutesUsedToday', 'minutesRemainingToday',
            'serviceAvailability', 'unavailableServices', 'systemOpenNow', 'todayHours'
        ));
    }}