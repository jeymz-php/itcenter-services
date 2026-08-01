<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Mail\AccountPendingMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
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

        if ($user->status === 'archived') {
            return response()->json(['message' => 'This account has been archived and cannot be accessed. Please contact the IT Center.'], 403);
        }
        if ($user->status === 'rejected') {
            return response()->json(['message' => 'Your account registration was rejected. Please contact the IT Center.'], 403);
        }

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
            'id_number'       => [
                'required', 'string', 'size:8',
                Rule::unique('users')->where(fn ($q) => $q->where('campus', $request->campus)),
            ],
            'first_name'      => 'required|string|max:100',
            'last_name'       => 'required|string|max:100',
            'email'           => 'required|email|unique:users',
            'campus'          => 'required|in:' . implode(',', $campuses),
            'user_type'       => 'required|in:' . implode(',', $userTypes),
            'password'        => [
                'required', 'string', 'min:8', 'confirmed',
                'regex:/[A-Z]/', 'regex:/[0-9]/',
            ],
            'profile_picture' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'terms'           => 'accepted',
        ]);

        $picPath = null;
        if ($request->hasFile('profile_picture')) {
            $picPath = $request->file('profile_picture')->store('profile_pictures', 'public');
        }

        $user = User::create([
            'id_number'       => $request->id_number,
            'first_name'      => $request->first_name,
            'last_name'       => $request->last_name,
            'email'           => $request->email,
            'profile_picture' => $picPath,
            'campus'          => $request->campus,
            'user_type'       => $request->user_type,
            'password'        => Hash::make($request->password),
            'status'          => 'pending',
        ]);

        $emailSent = true;
        try {
            Mail::to($user->email)->send(new AccountPendingMail($user, route('dashboard')));
        } catch (\Throwable $e) {
            $emailSent = false;
            Log::warning('API pending-account email could not be sent.', [
                'user_id' => $user->id,
                'email'   => $user->email,
                'error'   => $e->getMessage(),
            ]);
        }

        $token = $user->createToken('android-app')->plainTextToken;

        return response()->json([
            'token'                   => $token,
            'user'                    => $this->userPayload($user),
            'email_notification_sent' => $emailSent,
            'message'                 => 'Account created and pending administrator approval.',
        ], 201);
    }

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
                ? request()->getSchemeAndHttpHost() . '/storage/' . $user->profile_picture
                : null,
        ];
    }
}