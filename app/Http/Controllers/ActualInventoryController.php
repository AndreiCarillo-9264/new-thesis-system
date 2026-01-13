<?php

namespace App\Http\Controllers;

use App\Models\ActualInventory;
use App\Models\ActivityLog;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActualInventoryController extends Controller
{
    protected $table = 'actual_inventory';

    public function __construct()
    {
        $this->authorizeResource(ActualInventory::class, 'actual_inventory');
    }

    public function index()
    {
        $inventories = ActualInventory::with('product')
            ->orderBy('actual_quantity')
            ->paginate(20);
            
        return view('inventory.index', compact('inventories'));
    }

    public function create()
    {
        $productsWithoutInventory = Product::whereDoesntHave('actualInventory')->get();
        return view('inventory.create', compact('productsWithoutInventory'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id'       => 'required|exists:products,id|unique:actual_inventory,product_id',
            'actual_quantity'  => 'required|integer|min:0',
            'last_counted_at'  => 'nullable|date',
        ]);

        $inventory = ActualInventory::create($validated);

        ActivityLog::create([
            'user_id'   => Auth::id(),
            'action'    => 'created',
            'module'    => 'inventory',
            'record_id' => $inventory->id,
        ]);

        return redirect()->route('inventory.index')
            ->with('success', 'Initial inventory recorded.');
    }

    public function edit(ActualInventory $inventory)
    {
        return view('inventory.edit', compact('inventory'));
    }

    public function update(Request $request, ActualInventory $inventory)
    {
        $validated = $request->validate([
            'actual_quantity'  => 'required|integer|min:0',
            'last_counted_at'  => 'required|date',
        ]);

        $inventory->update($validated);

        ActivityLog::create([
            'user_id'   => Auth::id(),
            'action'    => 'updated',
            'module'    => 'inventory',
            'record_id' => $inventory->id,
        ]);

        return redirect()->route('inventory.index')
            ->with('success', 'Physical inventory count updated.');
    }

    // Usually no destroy – or very restricted
    public function destroy(ActualInventory $inventory)
    {
        // Very rare – maybe admin only in policy
        $inventory->delete();

        ActivityLog::create([
            'user_id'   => Auth::id(),
            'action'    => 'deleted',
            'module'    => 'inventory',
            'record_id' => $inventory->id,
        ]);

        return redirect()->route('inventory.index')
            ->with('success', 'Inventory record removed.');
    }
}