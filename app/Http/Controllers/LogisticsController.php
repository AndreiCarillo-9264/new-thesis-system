<?php
// New: app/Http/Controllers/LogisticsController.php
namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\ActualInventory;
use App\Models\Distribution;
use App\Models\InventoryTransfer;
use App\Models\JobOrder;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogisticsController extends Controller
{
    public function getDistributions()
    {
        $distributions = Distribution::with(['jobOrder.product'])
            ->latest('distribution_date')
            ->limit(10)
            ->get();
        return response()->json($distributions);
    }

    public function recordDistribution(Request $request)
    {
        $validated = $request->validate([
            'job_order_id' => 'required|exists:job_orders,id',
            'quantity_distributed' => 'required|integer|min:1',
            'distribution_date' => 'required|date',
            'destination' => 'required|string|max:255',
            'customer_name' => 'nullable|string|max:255',
            'driver' => 'nullable|string|max:255',
            'vehicle' => 'nullable|string|max:255',
            'status' => 'required|in:pending,in_transit,delivered',
            'notes' => 'nullable|string',
        ]);

        $jobOrder = JobOrder::findOrFail($validated['job_order_id']);
        $inventory = ActualInventory::where('product_id', $jobOrder->product_id)->firstOrFail();

        if ($inventory->actual_quantity < $validated['quantity_distributed']) {
            return response()->json(['success' => false, 'message' => 'Insufficient inventory'], 422);
        }

        $distribution = Distribution::create(array_merge($validated, [
            'product_id' => $jobOrder->product_id,
        ]));

        $inventory->decrement('actual_quantity', $validated['quantity_distributed']);

        if ($jobOrder->distributions->sum('quantity_distributed') + $validated['quantity_distributed'] >= $jobOrder->ordered_quantity) {
            $jobOrder->update(['status' => 'completed']);
        }

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'created',
            'module' => 'distributions',
            'record_id' => $distribution->id,
        ]);

        return response()->json(['success' => true, 'message' => 'Distribution recorded successfully']);
    }

    public function getTransfers()
    {
        $transfers = InventoryTransfer::with('product')->latest('transfer_date')->limit(10)->get();
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
            'reason' => 'nullable|in:Restock,Production Requirement,Optimization,Other',
            'notes' => 'nullable|string',
        ]);

        // For simplicity, assume global inventory; no per-location stock yet
        $inventory = ActualInventory::where('product_id', $validated['product_id'])->firstOrFail();

        if ($inventory->actual_quantity < $validated['quantity']) {
            return response()->json(['success' => false, 'message' => 'Insufficient inventory'], 422);
        }

        InventoryTransfer::create($validated);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'created',
            'module' => 'inventory_transfers',
            'record_id' => $transfer->id,
        ]);

        return response()->json(['success' => true, 'message' => 'Transfer recorded successfully']);
    }

    public function search(Request $request)
    {
        $search = $request->query('search', '');

        $distributions = Distribution::with(['jobOrder.product'])
            ->where(function ($q) use ($search) {
                $q->whereHas('jobOrder', function ($jo) use ($search) {
                    $jo->where('jo_number', 'like', "%{$search}%");
                })->orWhereHas('product', function ($p) use ($search) {
                    $p->where('product_name', 'like', "%{$search}%");
                })->orWhere('destination', 'like', "%{$search}%");
            })
            ->latest('distribution_date')
            ->limit(10)
            ->get();

        $transfers = InventoryTransfer::with('product')
            ->whereHas('product', function ($p) use ($search) {
                $p->where('product_name', 'like', "%{$search}%");
            })->orWhere('from_location', 'like', "%{$search}%")
              ->orWhere('to_location', 'like', "%{$search}%")
            ->latest('transfer_date')
            ->limit(10)
            ->get();

        return response()->json(['distributions' => $distributions, 'transfers' => $transfers]);
    }

    public function getCompletedJobOrders()
    {
        $jobOrders = JobOrder::whereIn('status', ['completed', 'in_progress'])
            ->with('product')
            ->get();
        return response()->json($jobOrders);
    }
}