<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\FinishedGood;
use App\Models\JobOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FinishedGoodController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(FinishedGood::class, 'finished_good');
    }

    public function index()
    {
        $finishedGoods = FinishedGood::with(['jobOrder', 'product'])->latest()->paginate(15);
        return view('finished_goods.index', compact('finishedGoods'));
    }

    public function create()
    {
        $jobOrders = JobOrder::whereIn('status', ['open', 'in_progress'])->get();
        return view('finished_goods.create', compact('jobOrders'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'job_order_id'     => 'required|exists:job_orders,id',
            'quantity_produced' => 'required|integer|min:1',
            'production_date'   => 'required|date',
        ]);

        $jobOrder = JobOrder::findOrFail($validated['job_order_id']);

        $currentTotal = $jobOrder->finishedGoods()->sum('quantity_produced');
        $newTotal = $currentTotal + $validated['quantity_produced'];

        if ($newTotal > $jobOrder->ordered_quantity) {
            $remaining = $jobOrder->ordered_quantity - $currentTotal;
            
            return back()
                ->withInput()
                ->withErrors([
                    'quantity_produced' => "Cannot exceed remaining ordered quantity ({$remaining} left)."
                ]);
        }

        $finishedGood = FinishedGood::create(array_merge($validated, [
            'product_id' => $jobOrder->product_id
        ]));

        // Update job order status automatically (optional)
        if ($newTotal >= $jobOrder->ordered_quantity) {
            $jobOrder->update(['status' => 'completed']);
        }

        ActivityLog::create([
            'user_id'   => Auth::id(),
            'action'    => 'created',
            'module'    => 'finished_goods',
            'record_id' => $finishedGood->id,
        ]);

        return redirect()->route('finished_goods.index')
            ->with('success', 'Production record added successfully.');
    }

    // edit(), update(), destroy() → similar pattern, with quantity validation
}