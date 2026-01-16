<?php
// New: app/Http/Controllers/ProductionController.php
namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\FinishedGood;
use App\Models\JobOrder;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductionController extends Controller
{
    public function getPendingOrders()
    {
        $orders = JobOrder::with('product')
            ->whereIn('status', ['open', 'in_progress'])
            ->latest()
            ->get();
        return response()->json($orders);
    }

    public function createJobOrder(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'product_id' => 'required|exists:products,id',
            'ordered_quantity' => 'required|integer|min:1',
            'priority' => 'required|in:high,medium,low',
            'due_date' => 'required|date',
            'assigned_team' => 'required|string|max:255',
        ]);

        $jo_number = $this->generateJoNumber();

        $order = JobOrder::create(array_merge($validated, [
            'jo_number' => $jo_number,
            'jo_date' => now(),
            'status' => 'open',
            'unit_price' => 0, // Default or add field if needed
            'user_id' => Auth::id(), // Assume current user
        ]));

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'created',
            'module' => 'job_orders',
            'record_id' => $order->id,
        ]);

        return response()->json(['success' => true, 'message' => 'Job Order created successfully', 'order' => $order]);
    }

    private function generateJoNumber()
    {
        $date = now()->format('Ym');
        $last = JobOrder::where('jo_number', 'like', "JO-{$date}%")->count() + 1;
        return "JO-{$date}-" . str_pad($last, 4, '0', STR_PAD_LEFT);
    }

    public function getFinishedGoods()
    {
        $finishedGoods = FinishedGood::with(['jobOrder.product'])->latest()->limit(10)->get();
        return response()->json($finishedGoods);
    }

    public function recordFinishedGoods(Request $request)
    {
        $validated = $request->validate([
            'job_order_id' => 'required|exists:job_orders,id',
            'quantity_produced' => 'required|integer|min:1',
            'production_date' => 'required|date',
        ]);

        $jobOrder = JobOrder::findOrFail($validated['job_order_id']);

        $currentTotal = $jobOrder->finishedGoods()->sum('quantity_produced');
        $newTotal = $currentTotal + $validated['quantity_produced'];

        if ($newTotal > $jobOrder->ordered_quantity) {
            return response()->json(['success' => false, 'message' => 'Cannot exceed ordered quantity'], 422);
        }

        $finishedGood = FinishedGood::create(array_merge($validated, [
            'product_id' => $jobOrder->product_id,
        ]));

        if ($newTotal >= $jobOrder->ordered_quantity) {
            $jobOrder->update(['status' => 'completed']);
        } else {
            $jobOrder->update(['status' => 'in_progress']);
        }

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'created',
            'module' => 'finished_goods',
            'record_id' => $finishedGood->id,
        ]);

        return response()->json(['success' => true, 'message' => 'Finished Goods recorded successfully']);
    }

    public function search(Request $request)
    {
        $search = $request->query('search', '');
        $status = $request->query('status', '');

        $pendingQuery = JobOrder::with('product')->whereIn('status', ['open', 'in_progress']);
        if ($search) {
            $pendingQuery->where(function ($q) use ($search) {
                $q->where('jo_number', 'like', "%{$search}%")
                  ->orWhereHas('product', function ($p) use ($search) {
                      $p->where('product_name', 'like', "%{$search}%");
                  });
            });
        }
        if ($status) {
            $pendingQuery->where('status', $status);
        }
        $pendingOrders = $pendingQuery->latest()->get();

        $finishedQuery = FinishedGood::with(['jobOrder.product']);
        if ($search) {
            $finishedQuery->where(function ($q) use ($search) {
                $q->whereHas('jobOrder', function ($jo) use ($search) {
                    $jo->where('jo_number', 'like', "%{$search}%");
                })->orWhereHas('product', function ($p) use ($search) {
                    $p->where('product_name', 'like', "%{$search}%");
                });
            });
        }
        $finishedGoods = $finishedQuery->latest()->limit(10)->get();

        return response()->json(['pending' => $pendingOrders, 'finished' => $finishedGoods]);
    }
}