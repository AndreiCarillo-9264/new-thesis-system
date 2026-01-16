<?php
// New: app/Http/Controllers/InventoryController.php
namespace App\Http\Controllers;

use App\Models\ActualInventory;
use App\Models\InventoryAudit;
use App\Models\InventoryTransfer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    public function getCurrentLevels()
    {
        $inventories = ActualInventory::with('product')->get();
        return response()->json($inventories);
    }

    public function adjustStock(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'adjustment_type' => 'required|in:add,remove',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $inventory = ActualInventory::firstOrCreate(['product_id' => $validated['product_id']], ['actual_quantity' => 0]);

        $newQuantity = $validated['adjustment_type'] === 'add' 
            ? $inventory->actual_quantity + $validated['quantity']
            : $inventory->actual_quantity - $validated['quantity'];

        if ($newQuantity < 0) {
            return response()->json(['success' => false, 'message' => 'Cannot adjust below zero'], 422);
        }

        $inventory->update([
            'actual_quantity' => $newQuantity,
            'last_counted_at' => now(),
        ]);

        InventoryAudit::create([
            'product_id' => $validated['product_id'],
            'adjustment_type' => $validated['adjustment_type'],
            'quantity' => $validated['quantity'],
            'reason' => $validated['reason'],
            'notes' => $validated['notes'],
            'user_id' => Auth::id(),
        ]);

        return response()->json(['success' => true, 'message' => 'Stock adjusted successfully']);
    }

    public function getRecentTransfers()
    {
        $transfers = InventoryTransfer::with('product')->latest()->limit(10)->get();
        return response()->json($transfers);
    }

    public function recordTransfer(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'from_location' => 'required|string|max:255',
            'to_location' => 'required|string|max:255|different:from_location',
            'transfer_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        // Optional: Check if sufficient at from_location, but since no per-location stock, skip for now

        InventoryTransfer::create($validated);

        return response()->json(['success' => true, 'message' => 'Transfer recorded successfully']);
    }

    public function search(Request $request)
    {
        $search = $request->query('search', '');
        $status = $request->query('status', '');

        $inventoryQuery = ActualInventory::with('product');
        if ($search) {
            $inventoryQuery->whereHas('product', function ($q) use ($search) {
                $q->where('product_code', 'like', "%{$search}%")
                  ->orWhere('product_name', 'like', "%{$search}%");
            });
        }
        if ($status) {
            $inventoryQuery->where('status', $status); // Assuming status appended in model
        }
        $inventories = $inventoryQuery->get();

        $transfersQuery = InventoryTransfer::with('product');
        if ($search) {
            $transfersQuery->whereHas('product', function ($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%");
            })->orWhere('from_location', 'like', "%{$search}%")
              ->orWhere('to_location', 'like', "%{$search}%");
        }
        $transfers = $transfersQuery->latest()->limit(10)->get();

        return response()->json(['inventory' => $inventories, 'transfers' => $transfers]);
    }
}