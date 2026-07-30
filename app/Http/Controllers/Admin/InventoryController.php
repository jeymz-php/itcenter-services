<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\InventoryStockLog;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    private function guard() {
        if (!session('admin')) abort(403);
        return session('admin');
    }

    private function assertInScope($admin, InventoryItem $item) {
        if ($admin->role !== 'super_admin' && $item->campus !== $admin->campus) {
            abort(403, 'You can only manage inventory from your own campus.');
        }
    }

    public function index(Request $request) {
        $admin = $this->guard();
        $viewCampus = $admin->role === 'super_admin' ? $request->campus : $admin->campus;

        $papersQuery    = InventoryItem::where('category','paper_size');
        $durationsQuery = InventoryItem::where('category','pc_duration');
        if ($viewCampus) {
            $papersQuery->where('campus', $viewCampus);
            $durationsQuery->where('campus', $viewCampus);
        }

        $papers    = $papersQuery->orderBy('campus')->orderBy('sort_order')->get();
        $durations = $durationsQuery->orderBy('campus')->orderBy('sort_order')->get();

        return view('admin.inventory.index', compact('papers','durations','viewCampus'));
    }

    public function store(Request $request) {
        $admin = $this->guard();
        $request->validate([
            'category'   => 'required|in:paper_size,pc_duration',
            'name'       => 'required|string|max:100',
            'value'      => 'required|string|max:50',
            'stock'      => 'required|integer|min:0',
            'campus'     => 'required|string',
            'is_active'  => 'nullable|boolean',
        ]);
        $campus = $admin->role === 'super_admin' ? $request->campus : $admin->campus;

        InventoryItem::create([
            'category'   => $request->category,
            'campus'     => $campus,
            'name'       => $request->name,
            'value'      => $request->value,
            'price'      => 0,
            'stock'      => $request->stock,
            'is_active'  => $request->boolean('is_active', true),
            'sort_order' => InventoryItem::where('category',$request->category)->where('campus',$campus)->max('sort_order') + 1,
        ]);
        return back()->with('success', 'Item added.');
    }

    public function update(Request $request, InventoryItem $inventoryItem) {
        $admin = $this->guard();
        $this->assertInScope($admin, $inventoryItem);
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
        $admin = $this->guard();
        $this->assertInScope($admin, $inventoryItem);
        $request->validate(['qty' => 'required|integer|min:1']);

        $inventoryItem->increment('stock', $request->qty);

        InventoryStockLog::create([
            'inventory_item_id' => $inventoryItem->id,
            'admin_id'          => $admin->id,
            'type'              => 'add',
            'quantity'          => $request->qty,
        ]);

        return back()->with('success', "Added {$request->qty} to {$inventoryItem->name}.");
    }

    // Reduces stock — for correcting an accidental over-addition. Requires
    // a reason (unlike Add Stock) since removing stock is the kind of
    // action worth being able to explain later, and can never take stock
    // below zero regardless of what quantity is requested.
    public function reduceStock(Request $request, InventoryItem $inventoryItem) {
        $admin = $this->guard();
        $this->assertInScope($admin, $inventoryItem);
        $request->validate([
            'qty'    => 'required|integer|min:1',
            'reason' => 'required|string|max:255',
        ]);

        if ($request->qty > $inventoryItem->stock) {
            return back()->withErrors([
                'error' => "Cannot reduce by {$request->qty} — {$inventoryItem->name} only has {$inventoryItem->stock} in stock."
            ]);
        }

        $inventoryItem->decrement('stock', $request->qty);

        InventoryStockLog::create([
            'inventory_item_id' => $inventoryItem->id,
            'admin_id'          => $admin->id,
            'type'              => 'reduce',
            'quantity'          => $request->qty,
            'note'              => $request->reason,
        ]);

        return back()->with('success', "Reduced {$inventoryItem->name} by {$request->qty}.");
    }

    // JSON feed for the "View" logs modal — kept lightweight (id, admin
    // name, type, qty, note, date) rather than a full page navigation,
    // since this is just a quick audit-trail lookup per item.
    public function logs(InventoryItem $inventoryItem) {
        $admin = $this->guard();
        $this->assertInScope($admin, $inventoryItem);

        $logs = $inventoryItem->stockLogs()->with('admin')->take(50)->get()->map(fn ($log) => [
            'type'       => $log->type,
            'quantity'   => $log->quantity,
            'note'       => $log->note,
            'admin_name' => $log->admin->admin_id ?? 'Unknown (removed)',
            'created_at' => $log->created_at->format('M d, Y g:i A'),
        ]);

        return response()->json(['item_name' => $inventoryItem->name, 'logs' => $logs]);
    }

    public function destroy(InventoryItem $inventoryItem) {
        $admin = $this->guard();
        $this->assertInScope($admin, $inventoryItem);
        $inventoryItem->delete();
        return back()->with('success', 'Item deleted.');
    }
}