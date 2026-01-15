<?php
// Updated: app/Http/Controllers/JobOrderController.php
namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\JobOrder;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JobOrderController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(JobOrder::class, 'job_order');
    }

    public function index()
    {
        $jobOrders = JobOrder::with('product')->latest()->paginate(15);
        return view('job_orders.index', compact('jobOrders'));
    }

    public function create()
    {
        $products = Product::where('is_active', true)->get();
        $users = User::all(); // Adjust if roles exist
        return view('job_orders.create', compact('products', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'jo_number'         => 'required|unique:job_orders,jo_number',
            'customer_name'     => 'required|string|max:255',
            'product_id'        => 'required|exists:products,id',
            'ordered_quantity'  => 'required|integer|min:1',
            'unit_price'        => 'required|numeric|min:0',
            'jo_date'           => 'required|date',
            'due_date'          => 'required|date|after_or_equal:jo_date',
            'status'            => 'sometimes|in:open,in_progress,completed,cancelled',
            'user_id'           => 'required|exists:users,id',
            'priority'          => 'required|in:high,medium,low',
            'notes'             => 'nullable|string',
        ]);

        $jobOrder = JobOrder::create(array_merge($validated, [
            'status' => $validated['status'] ?? 'open'
        ]));

        ActivityLog::create([
            'user_id'   => Auth::id(),
            'action'    => 'created',
            'module'    => 'job_orders',
            'record_id' => $jobOrder->id,
        ]);

        return redirect()->route('job_orders.index')
            ->with('success', 'Job Order created successfully.');
    }

    public function edit(JobOrder $jobOrder)
    {
        $products = Product::where('is_active', true)->get();
        $users = User::all();
        return view('job_orders.edit', compact('jobOrder', 'products', 'users'));
    }

    public function update(Request $request, JobOrder $jobOrder)
    {
        $validated = $request->validate([
            'customer_name'     => 'required|string|max:255',
            'product_id'        => 'required|exists:products,id',
            'ordered_quantity'  => 'required|integer|min:1',
            'unit_price'        => 'required|numeric|min:0',
            'jo_date'           => 'required|date',
            'due_date'          => 'required|date|after_or_equal:jo_date',
            'status'            => 'required|in:open,in_progress,completed,cancelled',
            'user_id'           => 'required|exists:users,id',
            'priority'          => 'required|in:high,medium,low',
            'notes'             => 'nullable|string',
        ]);

        $jobOrder->update($validated);

        
        if ($jobOrder->finishedGoods()->sum('quantity_produced') > $jobOrder->ordered_quantity) {
            session()->flash('warning', 'Warning: Produced quantity already exceeds ordered quantity.');
        }

        ActivityLog::create([
            'user_id'   => Auth::id(),
            'action'    => 'updated',
            'module'    => 'job_orders',
            'record_id' => $jobOrder->id,
        ]);

        return redirect()->route('job_orders.index')
            ->with('success', 'Job Order updated successfully.');
    }

    public function destroy(JobOrder $jobOrder)
    {

        if ($jobOrder->finishedGoods()->exists() || $jobOrder->distributions()->exists()) {
            return back()->with('error', 'Cannot delete Job Order with associated records.');
        }

        $jobOrder->delete();

        ActivityLog::create([
            'user_id'   => Auth::id(),
            'action'    => 'deleted',
            'module'    => 'job_orders',
            'record_id' => $jobOrder->id,
        ]);

        return redirect()->route('job_orders.index')
            ->with('success', 'Job Order deleted successfully.');
    }
}