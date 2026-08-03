<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class GuideController extends Controller
{
    public function markSeen()
    {
        $user = Auth::user();

        if (!$user->guide_seen_at) {
            $user->forceFill(['guide_seen_at' => now()])->save();
        }

        return response()->json(['ok' => true]);
    }
}
