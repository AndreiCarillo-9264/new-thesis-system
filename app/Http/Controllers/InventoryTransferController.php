<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\InventoryTransfer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryTransferController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(InventoryTransfer::class, 'transfer');
    }

    public function index()
    {
        $transfers = InventoryTransfer::with('product')
            ->latest()
            ->paginate(15);
            
        return view('transfers.index', compact('transfers'));
    }

    public function create()
    {
        $products = Product::where('is_active', true)->get();
        return view('transfers.create', compact('products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id'     => 'required|exists:products,id',
            'quantity'       => 'required|integer|min:1',
            'from_location'  => 'required|string|max:255',
            'to_location'    => 'required|string|max:255|different:from_location',
            'transfer_date'  => 'required|date',
        ]);

        $transfer = InventoryTransfer::create($validated);

        ActivityLog::create([
            'user_id'   => Auth::id(),
            'action'    => 'created',
            'module'    => 'inventory_transfers',
            'record_id' => $transfer->id,
        ]);

        return redirect()->route('transfers.index')
            ->with('success', 'Inventory transfer recorded.');
    }

    public function edit(InventoryTransfer $transfer)
    {
        $products = Product::where('is_active', true)->get();
        return view('transfers.edit', compact('transfer', 'products'));
    }

    public function update(Request $request, InventoryTransfer $transfer)
    {
        $validated = $request->validate([
            'product_id'     => 'required|exists:products,id',
            'quantity'       => 'required|integer|min:1',
            'from_location'  => 'required|string|max:255',
            'to_location'    => 'required|string|max:255|different:from_location',
            'transfer_date'  => 'required|date',
        ]);

        $transfer->update($validated);

        ActivityLog::create([
            'user_id'   => Auth::id(),
            'action'    => 'updated',
            'module'    => 'inventory_transfers',
            'record_id' => $transfer->id,
        ]);

        return redirect()->route('transfers.index')
            ->with('success', 'Transfer record updated.');
    }

    public function destroy(InventoryTransfer $transfer)
    {
        $transfer->delete();

        ActivityLog::create([
            'user_id'   => Auth::id(),
            'action'    => 'deleted',
            'module'    => 'inventory_transfers',
            'record_id' => $transfer->id,
        ]);

        return redirect()->route('transfers.index')
            ->with('success', 'Transfer record deleted.');
    }
}