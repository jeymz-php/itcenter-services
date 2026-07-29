<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\ResetPasswordMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    public function showForm() {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request) {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        $genericMessage = 'If that email address is registered, a password reset link has been sent to it.';

        if (!$user) {
            return back()->with('success', $genericMessage);
        }

        DB::table('password_resets')->where('email', $user->email)->delete();

        $plainToken = Str::random(64);
        DB::table('password_resets')->insert([
            'email'      => $user->email,
            'token'      => Hash::make($plainToken),
            'created_at' => now(),
        ]);

        $resetUrl = route('password.reset.form', [
            'token' => $plainToken,
            'email' => $user->email,
        ]);

        Mail::to($user->email)->send(new ResetPasswordMail($user->first_name, $resetUrl));

        return back()->with('success', $genericMessage);
    }
}