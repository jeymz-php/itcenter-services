<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    public function showLogin()  { return view('auth.login'); }
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

        // Blocked statuses — cannot log in at all
        if ($user->status === 'archived') {
            return back()->withErrors(['login' => 'This account has been archived and cannot be accessed. Please contact the IT Center.'])->withInput();
        }
        if ($user->status === 'rejected') {
            return back()->withErrors(['login' => 'Your account registration was rejected. Please contact the IT Center.'])->withInput();
        }

        // These statuses CAN log in but see restricted dashboard
        // pending, deactivated → allowed to login, shown notice on dashboard

        \Illuminate\Support\Facades\Auth::login($user);
        return redirect()->route('dashboard');
    }

    public function register(Request $request) {
        $campuses   = array_keys(config('campuses'));
        $userTypes  = ['student','faculty_staff'];

        $request->validate([
            'id_number'        => 'required|string|size:8|unique:users',
            'first_name'       => 'required|string|max:100',
            'last_name'        => 'required|string|max:100',
            'email'            => 'required|email|unique:users',
            'campus'           => 'required|in:'.implode(',',$campuses),
            'user_type'        => 'required|in:'.implode(',',$userTypes),
            'password'         => [
                'required','string','min:8','confirmed',
                'regex:/[A-Z]/','regex:/[0-9]/','regex:/[@$!%*#?&]/',
            ],
            'profile_picture'  => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
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

        Auth::login($user);
        return redirect()->route('dashboard');
    }

    public function logout(Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    public function dashboard() {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');
        return view('dashboard', compact('user'));
    }
}