<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AccountRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show(Request $request) {
        $user = Auth::user();

        $requests = AccountRequest::where('user_id', $user->id)
            ->latest()
            ->take(20)
            ->get(['id', 'type', 'status', 'reason', 'created_at']);

        return response()->json([
            'user'             => $this->userPayload($user),
            'account_requests' => $requests,
        ]);
    }

    // Handles both the "Edit Profile" text-field form and the standalone
    // "Change Photo" button — both hit this same endpoint, the photo is
    // simply optional either way. ID number is intentionally never
    // accepted here, matching the web version ("cannot be changed").
    public function update(Request $request) {
        $user = Auth::user();
        $campuses = array_keys(config('campuses'));

        $request->validate([
            'first_name'      => 'required|string|max:100',
            'last_name'       => 'required|string|max:100',
            'email'           => 'required|email|unique:users,email,' . $user->id,
            'campus'          => 'required|in:' . implode(',', $campuses),
            'profile_picture' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
        ]);

        $data = [
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'email'      => $request->email,
            'campus'     => $request->campus,
        ];

        if ($request->hasFile('profile_picture')) {
            if ($user->profile_picture) {
                Storage::disk('public')->delete($user->profile_picture);
            }
            $data['profile_picture'] = $request->file('profile_picture')->store('profile_pictures', 'public');
        }

        $user->update($data);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user'    => $this->userPayload($user->fresh()),
        ]);
    }

    public function updatePassword(Request $request) {
        $user = Auth::user();

        $request->validate([
            'current_password' => 'required|string',
            'password'         => [
                'required', 'string', 'min:8', 'confirmed',
                'regex:/[A-Z]/', 'regex:/[0-9]/',
            ],
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Current password is incorrect.'], 422);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return response()->json(['message' => 'Password changed successfully.']);
    }

    public function requestDeactivation(Request $request) {
        $user = Auth::user();

        $request->validate(['reason' => 'required|string|max:500']);

        if ($user->pendingRequest('deactivate')) {
            return response()->json(['message' => 'You already have a pending deactivation request.'], 422);
        }

        AccountRequest::create([
            'user_id' => $user->id,
            'type'    => 'deactivate',
            'reason'  => $request->reason,
            'status'  => 'pending',
        ]);

        return response()->json(['message' => 'Deactivation request submitted. An admin will review it shortly.']);
    }

    public function requestDeletion(Request $request) {
        $user = Auth::user();

        $request->validate(['reason' => 'required|string|max:500']);

        if ($user->pendingRequest('delete')) {
            return response()->json(['message' => 'You already have a pending deletion request.'], 422);
        }

        AccountRequest::create([
            'user_id' => $user->id,
            'type'    => 'delete',
            'reason'  => $request->reason,
            'status'  => 'pending',
        ]);

        return response()->json(['message' => 'Deletion request submitted. An admin will review it shortly.']);
    }

    private function userPayload(User $user): array {
        return [
            'id'                   => $user->id,
            'id_number'            => $user->id_number,
            'first_name'           => $user->first_name,
            'last_name'            => $user->last_name,
            'full_name'            => $user->full_name,
            'email'                => $user->email,
            'campus'               => $user->campus,
            'campus_label'         => config('campuses.' . $user->campus, $user->campus),
            'user_type'            => $user->user_type,
            'status'               => $user->status,
            'research_restricted'  => (bool) $user->research_restricted,
            'created_at'           => $user->created_at,
            'profile_picture_url'  => $user->profile_picture
                ? request()->getSchemeAndHttpHost() . '/storage/' . $user->profile_picture
                : null,
        ];
    }
}