<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ResetPasswordController extends Controller
{
    public function showForm(Request $request, string $token) {
        $email = $request->query('email');
        return view('auth.reset-password', compact('token', 'email'));
    }

    public function reset(Request $request) {
        $request->validate([
            'token'    => 'required|string',
            'email'    => 'required|email',
            'password' => [
                'required', 'string', 'min:8', 'confirmed',
                'regex:/[A-Z]/', 'regex:/[0-9]/',
            ],
        ]);

        $record = DB::table('password_resets')->where('email', $request->email)->first();

        if (!$record || !Hash::check($request->token, $record->token)) {
            return back()->withErrors(['token' => 'This password reset link is invalid. Please request a new one.']);
        }

        if (now()->diffInMinutes($record->created_at) > 60) {
            DB::table('password_resets')->where('email', $request->email)->delete();
            return back()->withErrors(['token' => 'This password reset link has expired. Please request a new one.']);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return back()->withErrors(['token' => 'No account found for this email address.']);
        }

        $user->update(['password' => Hash::make($request->password)]);
        DB::table('password_resets')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('success', 'Your password has been reset. Please sign in with your new password.');
    }
}