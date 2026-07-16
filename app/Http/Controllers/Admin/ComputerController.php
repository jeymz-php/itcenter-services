<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Computer;
use App\Models\AdminNotification;
use Illuminate\Http\Request;

class ComputerController extends Controller
{
    private function guard() {
        if (!session('admin')) abort(403);
        return session('admin');
    }

    // Regular admins are restricted to computers in their own campus; super admins see everyone.
    private function assertInScope($admin, Computer $computer) {
        if ($admin->role !== 'super_admin' && $computer->campus !== $admin->campus) {
            abort(403, 'You can only manage computers from your own campus.');
        }
    }

    public function index(Request $request) {
        $admin = $this->guard();
        $query = Computer::with('activeSession.user');

        if ($admin->role !== 'super_admin') {
            $query->where('campus', $admin->campus);
        } elseif ($request->campus) {
            $query->where('campus', $request->campus);
        }

        $computers = $query->orderBy('campus')->orderBy('sort_order')->get();
        return view('admin.computers.index', compact('computers'));
    }

    public function store(Request $request) {
        $admin = $this->guard();
        $request->validate([
            'name'   => 'required|string|max:50|unique:computers',
            'specs'  => 'nullable|string|max:300',
            'campus' => 'required|string',
        ]);
        // Regular admins can only add computers to their own campus, regardless of what was submitted
        $campus = $admin->role === 'super_admin' ? $request->campus : $admin->campus;

        $max = Computer::where('campus', $campus)->max('sort_order') ?? 0;
        Computer::create([
            'name'       => $request->name,
            'campus'     => $campus,
            'specs'      => $request->specs,
            'status'     => 'available',
            'sort_order' => $max + 1,
        ]);
        return back()->with('success', "Computer {$request->name} added successfully.");
    }

    public function update(Request $request, Computer $computer) {
        $admin = $this->guard();
        $this->assertInScope($admin, $computer);
        $request->validate([
            'name'  => 'required|string|max:50|unique:computers,name,'.$computer->id,
            'specs' => 'nullable|string|max:300',
        ]);
        $computer->update([
            'name'  => $request->name,
            'specs' => $request->specs,
        ]);
        return back()->with('success', "{$computer->name} updated successfully.");
    }

    public function activate(Computer $computer) {
        $admin = $this->guard();
        $this->assertInScope($admin, $computer);
        $computer->update([
            'status'            => 'available',
            'deactivation_note' => null,
        ]);
        return back()->with('success', "{$computer->name} has been activated.");
    }

    public function deactivate(Request $request, Computer $computer) {
        $admin = $this->guard();
        $this->assertInScope($admin, $computer);
        $request->validate(['note' => 'required|string|max:500']);
        $computer->update([
            'status'            => 'deactivated',
            'deactivation_note' => $request->note,
        ]);
        return back()->with('success', "{$computer->name} has been deactivated.");
    }

    public function destroy(Computer $computer) {
        $admin = $this->guard();
        $this->assertInScope($admin, $computer);
        if ($computer->status === 'in_use') {
            return back()->withErrors(['error' => 'Cannot delete a computer currently in use.']);
        }
        $name = $computer->name;
        $computer->delete();
        return back()->with('success', "{$name} has been deleted.");
    }
}