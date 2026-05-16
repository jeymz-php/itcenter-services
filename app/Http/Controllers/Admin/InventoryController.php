<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    private function guard() { if (!session('admin')) abort(403); }

    public function index() {
        $this->guard();
        $papers    = InventoryItem::where('category','paper_size')->orderBy('sort_order')->get();
        $durations = InventoryItem::where('category','pc_duration')->orderBy('sort_order')->get();
        return view('admin.inventory.index', compact('papers','durations'));
    }

    public function store(Request $request) {
        $this->guard();
        $request->validate([
            'category'   => 'required|in:paper_size,pc_duration',
            'name'       => 'required|string|max:100',
            'value'      => 'required|string|max:50',
            'stock'      => 'required|integer|min:0',
            'is_active'  => 'nullable|boolean',
        ]);
        InventoryItem::create([
            'category'   => $request->category,
            'name'       => $request->name,
            'value'      => $request->value,
            'price'      => 0,
            'stock'      => $request->stock,
            'is_active'  => $request->boolean('is_active', true),
            'sort_order' => InventoryItem::where('category',$request->category)->max('sort_order') + 1,
        ]);
        return back()->with('success', 'Item added.');
    }

    public function update(Request $request, InventoryItem $inventoryItem) {
        $this->guard();
        $request->validate([
            'name'      => 'required|string|max:100',
            'stock'     => 'required|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        $inventoryItem->update([
            'name'      => $request->name,
            'stock'     => $request->stock,
            'is_active' => $request->boolean('is_active', true),
        ]);
        return back()->with('success', 'Item updated.');
    }

    public function addStock(Request $request, InventoryItem $inventoryItem) {
        $this->guard();
        $request->validate(['qty' => 'required|integer|min:1']);
        $inventoryItem->increment('stock', $request->qty);
        return back()->with('success', "Added {$request->qty} to {$inventoryItem->name}.");
    }

    public function destroy(InventoryItem $inventoryItem) {
        $this->guard();
        $inventoryItem->delete();
        return back()->with('success', 'Item deleted.');
    }
}