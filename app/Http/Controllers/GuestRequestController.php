<?php
namespace App\Http\Controllers;

use App\Models\GuestRequest;
use App\Models\InventoryItem;
use App\Models\AdminNotification;
use Illuminate\Http\Request;

class GuestRequestController extends Controller
{
    public function index() {
        $paperSizes = InventoryItem::paperSizes();
        $durations  = InventoryItem::pcDurations();
        return view('public.request', compact('paperSizes','durations'));
    }

    public function store(Request $request) {
        $request->validate([
            'role'         => 'required|in:student,faculty_staff,visitor',
            'first_name'   => 'required|string|max:100',
            'last_name'    => 'required|string|max:100',
            'email'        => 'required|email|max:200',
            'campus'       => 'required|string',
            'id_number'    => 'nullable|string|max:20',
            'service_type' => [
                'required',
                function($attr, $val, $fail) use ($request) {
                    $allowed = ['printing','photocopy'];
                    if (in_array($request->role, ['student','faculty_staff'])) {
                        $allowed[] = 'research';
                    }
                    if (!in_array($val, $allowed)) {
                        $fail('The selected service type is invalid for your role.');
                    }
                }
            ],
            'purpose'      => 'required|string|max:500',
            'terms'        => 'accepted',
        ]);

        // Printing extra validation
        if ($request->service_type === 'printing') {
            $request->validate([
                'file'       => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
                'copies'     => 'required|integer|min:1|max:100',
                'paper_size' => 'required|string',
                'print_type' => 'required|in:black_white,colored',
            ]);
        }

        // Photocopy extra validation
        if ($request->service_type === 'photocopy') {
            $request->validate([
                'copies'     => 'required|integer|min:1|max:100',
                'paper_size' => 'required|string',
            ]);
        }

        // Research extra validation
        if ($request->service_type === 'research') {
            $request->validate([
                'duration_minutes' => 'required|integer|min:1',
            ]);
            if ($request->role === 'visitor') {
                return back()->withErrors(['service_type' => 'Visitors are not allowed to request Research/PC Lab services.'])->withInput();
            }
        }

        $data = [
            'request_number'   => GuestRequest::generateNumber(),
            'role'             => $request->role,
            'first_name'       => $request->first_name,
            'last_name'        => $request->last_name,
            'email'            => $request->email,
            'campus'           => $request->campus,
            'id_number'        => $request->id_number,
            'service_type'     => $request->service_type,
            'purpose'          => $request->purpose,
            'paper_size'       => $request->paper_size,
            'copies'           => $request->copies,
            'print_type'       => $request->print_type,
            'duration_minutes' => $request->duration_minutes,
        ];

        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('service_files','public');
            $data['file_name'] = $request->file('file')->getClientOriginalName();
        }

        $gr = GuestRequest::create($data);

        \App\Models\AdminNotification::create([
            'type'            => 'guest_request',
            'title'           => 'New Guest Request',
            'message'         => "{$gr->full_name} ({$gr->role}) submitted a {$gr->service_type} request ({$gr->request_number}).",
            'notifiable_id'   => 1,
            'notifiable_type' => 'App\\Models\\Admin',
            'action_url'      => route('admin.guest-requests.index'),
            'icon'            => 'fa-user-tag',
        ]);

        return redirect()->route('public.request.success', ['number' => $gr->request_number]);
    }

    public function success(Request $request) {
        $number = $request->query('number');
        $gr = GuestRequest::where('request_number', $number)->firstOrFail();
        return view('public.success', compact('gr'));
    }

    public function track(Request $request) {
        $gr = null;
        if ($request->filled('number')) {
            $gr = GuestRequest::where('request_number', $request->number)->first();
        }
        return view('public.track', compact('gr'));
    }
}