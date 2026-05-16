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
    }

    public function index() {
        $this->guard();
        $computers = Computer::with('activeSession.user')
                             ->orderBy('sort_order')
                             ->get();
        return view('admin.computers.index', compact('computers'));
    }

    public function store(Request $request) {
        $this->guard();
        $request->validate([
            'name'  => 'required|string|max:50|unique:computers',
            'specs' => 'nullable|string|max:300',
        ]);
        $max = Computer::max('sort_order') ?? 0;
        Computer::create([
            'name'       => $request->name,
            'specs'      => $request->specs,
            'status'     => 'available',
            'sort_order' => $max + 1,
        ]);
        return back()->with('success', "Computer {$request->name} added successfully.");
    }

    public function update(Request $request, Computer $computer) {
        $this->guard();
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
        $this->guard();
        $computer->update([
            'status'            => 'available',
            'deactivation_note' => null,
        ]);
        return back()->with('success', "{$computer->name} has been activated.");
    }

    public function deactivate(Request $request, Computer $computer) {
        $this->guard();
        $request->validate(['note' => 'required|string|max:500']);
        $computer->update([
            'status'            => 'deactivated',
            'deactivation_note' => $request->note,
        ]);
        return back()->with('success', "{$computer->name} has been deactivated.");
    }

    public function destroy(Computer $computer) {
        $this->guard();
        if ($computer->status === 'in_use') {
            return back()->withErrors(['error' => 'Cannot delete a computer currently in use.']);
        }
        $name = $computer->name;
        $computer->delete();
        return back()->with('success', "{$name} has been deleted.");
    }
}