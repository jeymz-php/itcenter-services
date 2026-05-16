<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\AccountRequest;
use App\Models\AdminNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function index() {
        $user = Auth::user();
        $requests = AccountRequest::where('user_id', $user->id)->latest()->get();
        return view('user.profile', compact('user','requests'));
    }

    public function changePassword(Request $request) {
        $request->validate([
            'current_password' => 'required|string',
            'password'         => 'required|string|min:8|confirmed|regex:/[A-Z]/|regex:/[0-9]/|regex:/[@$!%*#?&]/',
        ]);
        $user = Auth::user();
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }
        $user->update(['password' => Hash::make($request->password)]);
        return back()->with('success','Password changed successfully.');
    }

    public function updatePhoto(Request $request) {
        $request->validate(['profile_picture' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048']);
        $user = Auth::user();
        if ($user->profile_picture) Storage::disk('public')->delete($user->profile_picture);
        $path = $request->file('profile_picture')->store('profile_pictures','public');
        $user->update(['profile_picture' => $path]);
        return back()->with('success','Profile photo updated.');
    }

    public function update(Request $request) {
        $user = Auth::user();
        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'email'      => 'required|email|unique:users,email,'.$user->id,
            'campus'     => 'required|string',
        ]);
        $user->update($request->only('first_name','last_name','email','campus'));
        return back()->with('success', 'Profile updated successfully.');
    }

    public function requestDeactivate(Request $request) {
        $request->validate(['reason' => 'required|string|max:500']);
        $user = Auth::user();
        if ($user->pendingRequest('deactivate')) {
            return back()->withErrors(['error' => 'You already have a pending deactivation request.']);
        }
        AccountRequest::create([
            'user_id' => $user->id,
            'type'    => 'deactivate',
            'reason'  => $request->reason,
            'status'  => 'pending',
        ]);
        AdminNotification::notify(
            'deactivate_request','Deactivation Request',
            "{$user->full_name} requested to deactivate their account. Reason: {$request->reason}",
            $user, route('admin.users.index'), 'fa-user-slash'
        );
        return back()->with('success','Deactivation request submitted. Awaiting admin review.');
    }

    public function requestReactivate(Request $request) {
        $request->validate(['reason' => 'required|string|max:500']);
        $user = Auth::user();
        if ($user->pendingRequest('reactivate')) {
            return back()->withErrors(['error' => 'You already have a pending reactivation request.']);
        }
        AccountRequest::create([
            'user_id' => $user->id,
            'type'    => 'reactivate',
            'reason'  => $request->reason,
            'status'  => 'pending',
        ]);
        AdminNotification::notify(
            'reactivate_request','Reactivation Request',
            "{$user->full_name} requested to reactivate their account. Reason: {$request->reason}",
            $user, route('admin.users.index'), 'fa-user-check'
        );
        return back()->with('success','Reactivation request submitted.');
    }

    public function requestDelete(Request $request) {
        $request->validate(['reason' => 'required|string|max:500']);
        $user = Auth::user();
        if ($user->pendingRequest('delete')) {
            return back()->withErrors(['error' => 'You already have a pending deletion request.']);
        }
        AccountRequest::create([
            'user_id' => $user->id,
            'type'    => 'delete',
            'reason'  => $request->reason,
            'status'  => 'pending',
        ]);
        AdminNotification::notify(
            'delete_request','Account Deletion Request',
            "{$user->full_name} requested to delete their account. Reason: {$request->reason}",
            $user, route('admin.users.index'), 'fa-trash'
        );
        return back()->with('success','Account deletion request submitted. Awaiting admin approval.');
    }
}