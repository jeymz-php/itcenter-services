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

    public function transferToStudent(Request $request, Admin $admin) {
        $this->superAdminGuard();

        if ($admin->id === session('admin')->id) {
            return back()->withErrors(['error' => 'You cannot transfer your own account.']);
        }

        $campuses = array_keys(config('campuses'));

        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'id_number'  => [
                'required', 'string', 'size:8',
                \Illuminate\Validation\Rule::unique('users')->where(fn ($q) => $q->where('campus', $admin->campus)),
            ],
            'user_type'  => 'required|in:student,faculty_staff',
        ]);

        if (\App\Models\User::where('email', $admin->email)->exists()) {
            return back()->withErrors(['error' => "{$admin->email} is already used by an existing student/faculty account."]);
        }

        $tempPassword = \Illuminate\Support\Str::random(12);

        $user = \App\Models\User::create([
            'id_number'  => $request->id_number,
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'email'      => $admin->email,
            'campus'     => $admin->campus,
            'user_type'  => $request->user_type,
            'password'   => Hash::make($tempPassword),
            'status'     => 'active',
        ]);

        $admin->update(['status' => 'inactive']);

        AdminNotification::notify(
            'admin_demoted', 'Admin Transferred to Student Role',
            "{$admin->admin_id} ({$admin->email}) was transferred to a " . ($request->user_type === 'faculty_staff' ? 'Faculty/Staff' : 'Student') . " account by " . session('admin')->admin_id . ".",
            $user, route('admin.users.index'), 'fa-user-graduate'
        );

        return back()->with('success',
            "{$admin->admin_id} transferred to " . ($request->user_type === 'faculty_staff' ? 'Faculty/Staff' : 'Student') .
            " role — ID Number: {$request->id_number}, Temporary Password: {$tempPassword} (share this securely — it will not be shown again). Their admin account has been deactivated."
        );
    }
}