<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AdminNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends Controller
{
    private function adminGuard() {
        if (!session('admin')) abort(403);
        return session('admin');
    }

    // Regular admins are restricted to their own campus; super admins see everyone.
    private function assertInScope($admin, User $user) {
        if ($admin->role !== 'super_admin' && $user->campus !== $admin->campus) {
            abort(403, 'You can only manage users from your own campus.');
        }
    }

    public function index(Request $request) {
        $admin = $this->adminGuard();
        $query = User::query();
        if ($admin->role !== 'super_admin') {
            $query->where('campus', $admin->campus);
        }
        if ($request->search) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('first_name','like',"%$s%")
                  ->orWhere('last_name','like',"%$s%")
                  ->orWhere('id_number','like',"%$s%")
                  ->orWhere('email','like',"%$s%");
            });
        }
        if ($request->status)    $query->where('status', $request->status);
        if ($request->user_type) $query->where('user_type', $request->user_type);
        if ($request->campus && $admin->role === 'super_admin') $query->where('campus', $request->campus);

        $users = $query->latest()->paginate(15)->withQueryString();

        $countsBase = User::query();
        if ($admin->role !== 'super_admin') $countsBase->where('campus', $admin->campus);
        $countsBase = $countsBase->get();
        $counts = [
            'all'         => $countsBase->count(),
            'pending'     => $countsBase->where('status','pending')->count(),
            'active'      => $countsBase->where('status','active')->count(),
            'deactivated' => $countsBase->where('status','deactivated')->count(),
            'archived'    => $countsBase->where('status','archived')->count(),
            'rejected'    => $countsBase->where('status','rejected')->count(),
        ];
        return view('admin.users.index', compact('users','counts'));
    }

    public function show(User $user) {
        $admin = $this->adminGuard();
        $this->assertInScope($admin, $user);
        return view('admin.users.show', compact('user'));
    }

    public function approve(User $user) {
        $admin = $this->adminGuard();
        $this->assertInScope($admin, $user);
        $user->update(['status' => 'active']);
        AdminNotification::notify(
            'account_approved', 'Account Approved',
            "{$user->full_name} ({$user->id_number}) account has been approved.",
            $user, route('admin.users.index'), 'fa-user-check'
        );
        return back()->with('success', "Account of {$user->full_name} approved.");
    }

    public function reject(Request $request, User $user) {
        $admin = $this->adminGuard();
        $this->assertInScope($admin, $user);
        $request->validate(['reason' => 'required|string|max:500']);
        $user->update(['status' => 'rejected']);
        AdminNotification::notify(
            'account_rejected', 'Account Rejected',
            "{$user->full_name} ({$user->id_number}) account was rejected. Reason: {$request->reason}",
            $user, route('admin.users.index'), 'fa-user-xmark'
        );
        return back()->with('success', "Account of {$user->full_name} rejected.");
    }

    public function activate(User $user) {
        $admin = $this->adminGuard();
        $this->assertInScope($admin, $user);
        $user->update(['status' => 'active']);
        AdminNotification::notify(
            'account_activated','Account Activated',
            "{$user->full_name} account has been activated.",
            $user, route('admin.users.index'), 'fa-user-check'
        );
        return back()->with('success', "Account activated.");
    }

    public function deactivate(Request $request, User $user) {
        $admin = $this->adminGuard();
        $this->assertInScope($admin, $user);
        $request->validate(['reason' => 'nullable|string|max:500']);
        $user->update(['status' => 'deactivated']);
        AdminNotification::notify(
            'account_deactivated','Account Deactivated',
            "{$user->full_name} account has been deactivated.",
            $user, route('admin.users.index'), 'fa-user-slash'
        );
        return back()->with('success', "Account deactivated.");
    }

    public function archive(User $user) {
        $admin = $this->adminGuard();
        $this->assertInScope($admin, $user);
        $user->update(['status' => 'archived']);
        AdminNotification::notify(
            'account_archived','Account Archived',
            "{$user->full_name} account has been archived.",
            $user, route('admin.users.index'), 'fa-box-archive'
        );
        return back()->with('success', "Account archived.");
    }

    public function store(Request $request) {
        $admin = $this->adminGuard();
        $request->validate([
            'id_number'  => 'required|string|size:8|unique:users',
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'email'      => 'required|email|unique:users',
            'campus'     => 'required|string',
            'user_type'  => 'required|in:student,faculty_staff',
            'password'   => 'required|string|min:8',
        ]);
        // Regular admins can only create users in their own campus, regardless of what was submitted
        $campus = $admin->role === 'super_admin' ? $request->campus : $admin->campus;

        $user = User::create([
            'id_number'  => $request->id_number,
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'email'      => $request->email,
            'campus'     => $campus,
            'user_type'  => $request->user_type,
            'password'   => Hash::make($request->password),
            'status'     => 'active',
        ]);
        AdminNotification::notify(
            'user_created','New User Created',
            "Admin created account for {$user->full_name}.",
            $user, route('admin.users.index'), 'fa-user-plus'
        );
        return back()->with('success', "User {$user->full_name} created successfully.");
    }

    public function update(Request $request, User $user) {
        $admin = $this->adminGuard();
        $this->assertInScope($admin, $user);
        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'email'      => 'required|email|unique:users,email,'.$user->id,
            'campus'     => 'required|string',
            'user_type'  => 'required|in:student,faculty_staff',
        ]);
        $data = $request->only('first_name','last_name','email','campus','user_type');
        // Regular admins cannot move a user to a different campus
        if ($admin->role !== 'super_admin') $data['campus'] = $admin->campus;
        $user->update($data);
        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }
        return back()->with('success', "User updated successfully.");
    }

    public function destroy(User $user) {
        $admin = $this->adminGuard();
        $this->assertInScope($admin, $user);
        $name = $user->full_name;
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', "User {$name} deleted.");
    }

    // Restrict a student/faculty account from submitting Research/PC-Lab requests
    // (e.g. following a report of inappropriate computer use). Everything else
    // on their account keeps working — this only blocks that one service.
    public function restrictResearch(Request $request, User $user) {
        $admin = $this->adminGuard();
        $this->assertInScope($admin, $user);
        $request->validate(['reason' => 'required|string|max:500']);

        $user->update([
            'research_restricted'      => true,
            'research_restriction_note'=> $request->reason,
        ]);

        AdminNotification::notify(
            'research_restricted','Research/PC-Lab Access Restricted',
            "{$user->full_name}'s access to Research/PC-Lab requests has been restricted. Reason: {$request->reason}",
            $user, route('admin.users.index'), 'fa-desktop'
        );

        return back()->with('success', "{$user->full_name} is now restricted from Research/PC-Lab requests.");
    }

    public function unrestrictResearch(User $user) {
        $admin = $this->adminGuard();
        $this->assertInScope($admin, $user);

        $user->update([
            'research_restricted'      => false,
            'research_restriction_note'=> null,
        ]);

        AdminNotification::notify(
            'research_unrestricted','Research/PC-Lab Access Restored',
            "{$user->full_name}'s access to Research/PC-Lab requests has been restored.",
            $user, route('admin.users.index'), 'fa-desktop'
        );

        return back()->with('success', "{$user->full_name}'s Research/PC-Lab access has been restored.");
    }

    // Promote a student/faculty account to an Admin account. This creates a
    // brand-new record in the separate `admins` table (students/faculty and
    // admins are entirely different login systems in this app) and archives
    // the original User record so the same person isn't simultaneously a
    // logged-in student AND an admin — their old service-request history
    // stays intact under the archived account for records purposes.
    //
    // Security boundary: a regular Admin can only promote someone to the
    // 'admin' role, within their own campus. Only a Super Admin can grant
    // the 'super_admin' role — this mirrors the existing rule that only
    // Super Admins can manage the admins table at all, so a regular Admin
    // can never mint a new Super Admin (themselves or anyone else).
    public function transferRole(Request $request, User $user) {
        $admin = $this->adminGuard();
        $this->assertInScope($admin, $user);

        $request->validate([
            'role' => 'required|in:admin,super_admin',
        ]);

        if ($request->role === 'super_admin' && $admin->role !== 'super_admin') {
            abort(403, 'Only a Super Admin can grant Super Admin access.');
        }

        if (\App\Models\Admin::where('email', $user->email)->exists()) {
            return back()->withErrors(['error' => "{$user->email} is already used by an existing admin account."]);
        }

        // Generate a unique ADMIN### id
        $next = \App\Models\Admin::count() + 1;
        do {
            $adminId = 'ADMIN' . str_pad($next, 3, '0', STR_PAD_LEFT);
            $next++;
        } while (\App\Models\Admin::where('admin_id', $adminId)->exists());

        // One-time random password — shown once in the success message below.
        // The user has no existing password we can safely carry over (we only
        // have the hash, not the plaintext), and reusing a student password
        // for a newly-privileged admin account isn't good practice anyway.
        $tempPassword = \Illuminate\Support\Str::random(12);

        $newAdmin = \App\Models\Admin::create([
            'admin_id' => $adminId,
            'email'    => $user->email,
            'campus'   => $user->campus,
            'role'     => $request->role,
            'password' => \Illuminate\Support\Facades\Hash::make($tempPassword),
        ]);

        $user->update(['status' => 'archived']);

        AdminNotification::notify(
            'user_promoted','User Promoted to Admin',
            "{$user->full_name} ({$user->id_number}) was promoted to ".($request->role==='super_admin'?'Super Admin':'Admin')." as {$adminId} by {$admin->admin_id}.",
            $user, route('admin.admins.index'), 'fa-user-shield'
        );

        return back()->with('success',
            "{$user->full_name} promoted to " . ($request->role === 'super_admin' ? 'Super Admin' : 'Admin') .
            " — Admin ID: {$adminId}, Temporary Password: {$tempPassword} (share this securely — it will not be shown again). Their student account has been archived."
        );
    }

    public function approveRequest(Request $request, \App\Models\AccountRequest $accountRequest) {
        $admin = $this->adminGuard();
        $user = $accountRequest->user;
        $this->assertInScope($admin, $user);
        $newStatus = match($accountRequest->type) {
            'deactivate'  => 'deactivated',
            'reactivate'  => 'active',
            'delete'      => null,
            default       => $user->status,
        };
        $accountRequest->update([
            'status'      => 'approved',
            'reviewed_by' => session('admin')->id,
            'reviewed_at' => now(),
        ]);
        if ($accountRequest->type === 'delete') {
            $user->delete();
            return redirect()->route('admin.users.index')->with('success','User account deleted per request.');
        }
        $user->update(['status' => $newStatus]);
        AdminNotification::notify(
            'request_approved','Account Request Approved',
            "{$accountRequest->type} request for {$user->full_name} approved.",
            $user, route('admin.users.index'), 'fa-circle-check'
        );
        return back()->with('success','Request approved.');
    }

    public function rejectRequest(Request $request, \App\Models\AccountRequest $accountRequest) {
        $admin = $this->adminGuard();
        $this->assertInScope($admin, $accountRequest->user);
        $request->validate(['admin_note' => 'nullable|string|max:500']);
        $accountRequest->update([
            'status'      => 'rejected',
            'reviewed_by' => session('admin')->id,
            'reviewed_at' => now(),
            'admin_note'  => $request->admin_note,
        ]);
        return back()->with('success','Request rejected.');
    }
}