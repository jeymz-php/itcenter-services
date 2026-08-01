<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Rating;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RatingController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'service_request_id' => 'required|exists:service_requests,id',
            'visibility'         => 'required|in:public,anonymous',
            'stars'              => 'required|integer|min:1|max:5',
            'comment'            => 'nullable|string|max:1000',
            'suggestions'        => 'nullable|string|max:1000',
        ]);

        $serviceRequest = ServiceRequest::findOrFail($request->service_request_id);

        if ($serviceRequest->user_id !== Auth::id()) {
            abort(403);
        }
        if ($serviceRequest->status !== 'completed') {
            return back()->withErrors(['stars' => 'Only completed requests can be rated.']);
        }
        if ($serviceRequest->rating()->exists()) {
            return back()->withErrors(['stars' => 'This request has already been rated.']);
        }

        Rating::create([
            'service_request_id' => $serviceRequest->id,
            'user_id'            => Auth::id(),
            'is_anonymous'       => $request->visibility === 'anonymous',
            'stars'              => $request->stars,
            'comment'            => $request->comment,
            'suggestions'        => $request->suggestions,
        ]);

        $serviceRequest->forceFill([
            'rating_prompted_at'  => $serviceRequest->rating_prompted_at ?: now(),
            'rating_dismissed_at' => null,
        ])->save();

        return back()->with('success', 'Thank you for your feedback!');
    }

    public function dismiss(Request $request, ServiceRequest $serviceRequest)
    {
        if ($serviceRequest->user_id !== Auth::id()) {
            abort(403);
        }
        if ($serviceRequest->status !== 'completed') {
            return response()->json(['message' => 'Only completed requests can dismiss a rating prompt.'], 422);
        }

        if (!$serviceRequest->rating()->exists()) {
            $serviceRequest->forceFill([
                'rating_prompted_at'  => $serviceRequest->rating_prompted_at ?: now(),
                'rating_dismissed_at' => now(),
            ])->save();
        }

        return $request->expectsJson()
            ? response()->json(['message' => 'The review prompt was dismissed.'])
            : back();
    }
}
