<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Mirrors the exact same field combo and business rules as the web
    // AuthController::login() — a student/faculty account is identified by
    // (id_number + campus + user_type) together, not id_number alone, since
    // the same ID number pattern can theoretically repeat across campuses.
    public function login(Request $request) {
        $request->validate([
            'campus'    => 'required|string',
            'user_type' => 'required|string',
            'id_number' => 'required|string',
            'password'  => 'required|string',
        ]);

        $user = User::where('id_number', $request->id_number)
                     ->where('campus', $request->campus)
                     ->where('user_type', $request->user_type)
                     ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials. Please try again.'], 401);
        }

        // Blocked statuses — cannot log in at all, same as web
        if ($user->status === 'archived') {
            return response()->json(['message' => 'This account has been archived and cannot be accessed. Please contact the IT Center.'], 403);
        }
        if ($user->status === 'rejected') {
            return response()->json(['message' => 'Your account registration was rejected. Please contact the IT Center.'], 403);
        }

        // pending / deactivated are allowed to log in (app should show a
        // restricted/notice screen client-side based on `status`, same as
        // the web dashboard does) — everything else proceeds normally.
        $token = $user->createToken('android-app')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => $this->userPayload($user),
        ]);
    }

    public function register(Request $request) {
        $campuses  = array_keys(config('campuses'));
        $userTypes = ['student', 'faculty_staff'];

        $request->validate([
            'id_number'  => 'required|string|size:8|unique:users',
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'email'      => 'required|email|unique:users',
            'campus'     => 'required|in:' . implode(',', $campuses),
            'user_type'  => 'required|in:' . implode(',', $userTypes),
            'password'   => [
                'required', 'string', 'min:8', 'confirmed',
                'regex:/[A-Z]/', 'regex:/[0-9]/', 'regex:/[@$!%*#?&]/',
            ],
            'terms' => 'accepted',
        ]);

        $user = User::create([
            'id_number'  => $request->id_number,
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'email'      => $request->email,
            'campus'     => $request->campus,
            'user_type'  => $request->user_type,
            'password'   => Hash::make($request->password),
            'status'     => 'pending',
        ]);

        $token = $user->createToken('android-app')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => $this->userPayload($user),
        ], 201);
    }

    // Revokes only the token used for this request — logging out on the
    // phone doesn't affect a session on the web or a token issued to
    // another device.
    public function logout(Request $request) {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out.']);
    }

    public function me(Request $request) {
        return response()->json(['user' => $this->userPayload($request->user())]);
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
            'profile_picture_url'  => $user->profile_picture
                ? \Illuminate\Support\Facades\Storage::url($user->profile_picture)
                : null,
        ];
    }
}