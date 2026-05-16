<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminManagementController extends Controller
{
    private function superAdminGuard() {
        $admin = session('admin');
        if (!$admin || $admin->role !== 'super_admin') abort(403, 'Super Admin access only.');
    }

    public function index() {
        $this->superAdminGuard();
        $admins = Admin::latest()->get();
        return view('admin.admins.index', compact('admins'));
    }

    public function store(Request $request) {
        $this->superAdminGuard();
        $request->validate([
            'admin_id'   => 'required|string|unique:admins',
            'email'      => 'required|email|unique:admins',
            'campus'     => 'required|string',
            'role'       => 'required|in:admin,super_admin',
            'password'   => 'required|string|min:8',
        ]);
        $admin = Admin::create([
            'admin_id' => $request->admin_id,
            'email'    => $request->email,
            'campus'   => $request->campus,
            'role'     => $request->role,
            'password' => Hash::make($request->password),
        ]);
        AdminNotification::notify(
            'admin_created','New Admin Added',
            "Super Admin created a new {$admin->role} account: {$admin->admin_id}.",
            $admin, route('admin.admins.index'), 'fa-user-shield'
        );
        return back()->with('success', "Admin {$admin->admin_id} created.");
    }

    public function update(Request $request, Admin $admin) {
        $this->superAdminGuard();
        $request->validate([
            'email'  => 'required|email|unique:admins,email,'.$admin->id,
            'campus' => 'required|string',
            'role'   => 'required|in:admin,super_admin',
        ]);
        $admin->update($request->only('email','campus','role'));
        if ($request->filled('password')) {
            $admin->update(['password' => Hash::make($request->password)]);
        }
        return back()->with('success','Admin updated.');
    }

    public function destroy(Admin $admin) {
        $this->superAdminGuard();
        if ($admin->id === session('admin')->id) {
            return back()->withErrors(['error' => 'You cannot delete your own account.']);
        }
        $admin->delete();
        return back()->with('success','Admin deleted.');
    }

    public function toggleStatus(Admin $admin) {
        $this->superAdminGuard();
        $newStatus = ($admin->status ?? 'active') === 'active' ? 'inactive' : 'active';
        $admin->update(['status' => $newStatus]);
        return back()->with('success', "Admin account {$newStatus}.");
    }
}