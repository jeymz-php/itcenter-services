<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class AdminAuthController extends Controller
{
    public function showLogin() {
        return view('auth.admin-login');
    }

    public function login(Request $request) {
        $request->validate([
            'admin_id' => 'required|string',
            'password' => 'required|string',
            'campus'   => 'required|string',
        ]);

        $admin = Admin::where(function($q) use ($request) {
                        $q->where('admin_id', $request->admin_id)
                          ->orWhere('email', $request->admin_id);
                    })
                    ->where('campus', $request->campus)
                    ->first();

        if ($admin && Hash::check($request->password, $admin->password)) {
            Session::put('admin', $admin);
            return redirect()->route('admin.dashboard');
        }

        return back()->withErrors(['login' => 'Invalid admin credentials.'])->withInput();
    }

    public function logout(Request $request) {
        Session::forget('admin');
        return redirect()->route('admin.login');
    }

    public function dashboard() {
        if (!Session::has('admin')) {
            return redirect()->route('admin.login');
        }
        return view('admin.dashboard');
    }
}