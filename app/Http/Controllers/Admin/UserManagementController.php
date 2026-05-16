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
    }

    public function index(Request $request) {
        $this->adminGuard();
        $query = User::query();
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
        if ($request->campus)    $query->where('campus', $request->campus);

        $users = $query->latest()->paginate(15)->withQueryString();
        $counts = [
            'all'         => User::count(),
            'pending'     => User::where('status','pending')->count(),
            'active'      => User::where('status','active')->count(),
            'deactivated' => User::where('status','deactivated')->count(),
            'archived'    => User::where('status','archived')->count(),
            'rejected'    => User::where('status','rejected')->count(),
        ];
        return view('admin.users.index', compact('users','counts'));
    }

    public function show(User $user) {
        $this->adminGuard();
        return view('admin.users.show', compact('user'));
    }

    public function approve(User $user) {
        $this->adminGuard();
        $user->update(['status' => 'active']);
        AdminNotification::notify(
            'account_approved', 'Account Approved',
            "{$user->full_name} ({$user->id_number}) account has been approved.",
            $user, route('admin.users.index'), 'fa-user-check'
        );
        return back()->with('success', "Account of {$user->full_name} approved.");
    }

    public function reject(Request $request, User $user) {
        $this->adminGuard();
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
        $this->adminGuard();
        $user->update(['status' => 'active']);
        AdminNotification::notify(
            'account_activated','Account Activated',
            "{$user->full_name} account has been activated.",
            $user, route('admin.users.index'), 'fa-user-check'
        );
        return back()->with('success', "Account activated.");
    }

    public function deactivate(Request $request, User $user) {
        $this->adminGuard();
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
        $this->adminGuard();
        $user->update(['status' => 'archived']);
        AdminNotification::notify(
            'account_archived','Account Archived',
            "{$user->full_name} account has been archived.",
            $user, route('admin.users.index'), 'fa-box-archive'
        );
        return back()->with('success', "Account archived.");
    }

    public function store(Request $request) {
        $this->adminGuard();
        $request->validate([
            'id_number'  => 'required|string|size:8|unique:users',
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'email'      => 'required|email|unique:users',
            'campus'     => 'required|string',
            'user_type'  => 'required|in:student,faculty_staff',
            'password'   => 'required|string|min:8',
        ]);
        $user = User::create([
            'id_number'  => $request->id_number,
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'email'      => $request->email,
            'campus'     => $request->campus,
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
        $this->adminGuard();
        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'email'      => 'required|email|unique:users,email,'.$user->id,
            'campus'     => 'required|string',
            'user_type'  => 'required|in:student,faculty_staff',
        ]);
        $user->update($request->only('first_name','last_name','email','campus','user_type'));
        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }
        return back()->with('success', "User updated successfully.");
    }

    public function destroy(User $user) {
        $this->adminGuard();
        $name = $user->full_name;
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', "User {$name} deleted.");
    }

    public function approveRequest(Request $request, \App\Models\AccountRequest $accountRequest) {
        $this->adminGuard();
        $user = $accountRequest->user;
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
        $this->adminGuard();
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