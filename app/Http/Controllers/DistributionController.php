<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Distribution;
use App\Models\JobOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DistributionController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Distribution::class, 'distribution');
    }

    public function index()
    {
        $distributions = Distribution::with(['jobOrder.product'])
            ->latest()
            ->paginate(15);
            
        return view('distributions.index', compact('distributions'));
    }

    public function create()
    {
        // Usually we distribute from completed/in-progress job orders
        $jobOrders = JobOrder::whereIn('status', ['in_progress', 'completed'])
            ->with('product')
            ->get();
            
        return view('distributions.create', compact('jobOrders'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'job_order_id'         => 'required|exists:job_orders,id',
            'quantity_distributed' => 'required|integer|min:1',
            'distribution_date'    => 'required|date',
            'destination'          => 'required|string|max:255',
        ]);

        $jobOrder = JobOrder::findOrFail($validated['job_order_id']);

        $totalProduced = $jobOrder->finishedGoods()->sum('quantity_produced');
        $totalAlreadyDistributed = $jobOrder->distributions()->sum('quantity_distributed');

        $newTotalDistributed = $totalAlreadyDistributed + $validated['quantity_distributed'];

        if ($newTotalDistributed > $totalProduced) {
            return back()
                ->withInput()
                ->withErrors([
                    'quantity_distributed' => "Cannot distribute more than produced quantity. "
                        . "Produced: {$totalProduced}, Already distributed: {$totalAlreadyDistributed}, "
                        . "Remaining: " . ($totalProduced - $totalAlreadyDistributed)
                ]);
        }

        $distribution = Distribution::create(array_merge($validated, [
            'product_id' => $jobOrder->product_id
        ]));

        ActivityLog::create([
            'user_id'   => Auth::id(),
            'action'    => 'created',
            'module'    => 'distributions',
            'record_id' => $distribution->id,
        ]);

        return redirect()->route('distributions.index')
            ->with('success', 'Distribution record created successfully.');
    }

    public function edit(Distribution $distribution)
    {
        $jobOrders = JobOrder::whereIn('status', ['in_progress', 'completed'])->get();
        return view('distributions.edit', compact('distribution', 'jobOrders'));
    }

    public function update(Request $request, Distribution $distribution)
    {
        $validated = $request->validate([
            'job_order_id'         => 'required|exists:job_orders,id',
            'quantity_distributed' => 'required|integer|min:1',
            'distribution_date'    => 'required|date',
            'destination'          => 'required|string|max:255',
        ]);

        $jobOrder = JobOrder::findOrFail($validated['job_order_id']);

        // Calculate remaining after this update
        $totalProduced = $jobOrder->finishedGoods()->sum('quantity_produced');
        
        $otherDistributions = $jobOrder->distributions()
            ->where('id', '!=', $distribution->id)
            ->sum('quantity_distributed');

        $newTotal = $otherDistributions + $validated['quantity_distributed'];

        if ($newTotal > $totalProduced) {
            return back()
                ->withInput()
                ->withErrors([
                    'quantity_distributed' => "Updated quantity would exceed produced amount."
                ]);
        }

        $distribution->update($validated);

        ActivityLog::create([
            'user_id'   => Auth::id(),
            'action'    => 'updated',
            'module'    => 'distributions',
            'record_id' => $distribution->id,
        ]);

        return redirect()->route('distributions.index')
            ->with('success', 'Distribution updated successfully.');
    }

    public function destroy(Distribution $distribution)
    {
        $distribution->delete();

        ActivityLog::create([
            'user_id'   => Auth::id(),
            'action'    => 'deleted',
            'module'    => 'distributions',
            'record_id' => $distribution->id,
        ]);

        return redirect()->route('distributions.index')
            ->with('success', 'Distribution record deleted.');
    }
}