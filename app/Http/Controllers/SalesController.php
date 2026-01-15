<?php
// New: app/Http/Controllers/SalesController.php
namespace App\Http\Controllers;

use App\Exports\SalesExport;
use App\Models\ActivityLog;
use App\Models\JobOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class SalesController extends Controller
{
    public function index()
    {
        $orders = JobOrder::with(['product', 'salesRep'])->latest()->get();
        return response()->json($orders);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name'     => 'required|string|max:255',
            'product_id'        => 'required|exists:products,id',
            'ordered_quantity'  => 'required|integer|min:1',
            'unit_price'        => 'required|numeric|min:0',
            'due_date'          => 'required|date',
            'user_id'           => 'required|exists:users,id',
            'priority'          => 'required|in:high,medium,low',
        ]);

        $jo_number = $this->generateJoNumber();

        $order = JobOrder::create(array_merge($validated, [
            'jo_number' => $jo_number,
            'jo_date' => now(),
            'status' => 'open',
        ]));

        ActivityLog::create([
            'user_id'   => Auth::id(),
            'action'    => 'created',
            'module'    => 'job_orders',
            'record_id' => $order->id,
        ]);

        return response()->json(['success' => true, 'message' => 'Order created successfully', 'order' => $order]);
    }

    private function generateJoNumber()
    {
        $date = now()->format('Ym');
        $last = JobOrder::where('jo_number', 'like', "JO-{$date}%")->count() + 1;
        return "JO-{$date}-" . str_pad($last, 4, '0', STR_PAD_LEFT);
    }

    public function search(Request $request)
    {
        $query = JobOrder::with(['product', 'salesRep']);

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('jo_number', 'like', "%{$search}%")
                ->orWhere('customer_name', 'like', "%{$search}%")
                ->orWhereHas('product', function ($p) use ($search) {
                    $p->where('product_name', 'like', "%{$search}%");
                })
                ->orWhereHas('salesRep', function ($u) use ($search) {
                    $u->where('name', 'like', "%{$search}%");
                });
            });
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $orders = $query->latest()->get();

        $distributionQuery = Distribution::with(['jobOrder.product', 'jobOrder.salesRep']);

        if ($search = $request->query('search')) {
            $distributionQuery->whereHas('jobOrder', function ($q) use ($search) {
                $q->where('jo_number', 'like', "%{$search}%")
                ->orWhere('customer_name', 'like', "%{$search}%")
                ->orWhereHas('product', function ($p) use ($search) {
                    $p->where('product_name', 'like', "%{$search}%");
                })
                ->orWhereHas('salesRep', function ($u) use ($search) {
                    $u->where('name', 'like', "%{$search}%");
                });
            });
        }

        if ($status = $request->query('status')) {
            $distributionQuery->whereHas('jobOrder', function ($q) use ($status) {
                $q->where('status', $status);
            });
        }

        $distributions = $distributionQuery->latest()->get();

        return response()->json(['orders' => $orders, 'distributions' => $distributions]);
    }

    public function generateReport(Request $request)
    {
        $validated = $request->validate([
            'report_type' => 'required|in:Sales Summary,Detailed Report,Customer Report',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'format' => 'required|in:PDF,Excel,CSV',
        ]);

        $start = $validated['start_date'];
        $end = $validated['end_date'];
        $type = $validated['report_type'];
        $format = $validated['format'];

        if ($type === 'Detailed Report') {
            $data = JobOrder::with(['product', 'salesRep'])
                ->whereBetween('jo_date', [$start, $end])
                ->latest()
                ->get();
        } elseif ($type === 'Sales Summary') {
            $data = [
                'total_orders' => JobOrder::whereBetween('jo_date', [$start, $end])->count(),
                'total_revenue' => JobOrder::whereBetween('jo_date', [$start, $end])->sum(\DB::raw('ordered_quantity * unit_price')),
            ];
        } elseif ($type === 'Customer Report') {
            $data = JobOrder::select('customer_name', \DB::raw('COUNT(*) as orders_count'), \DB::raw('SUM(ordered_quantity * unit_price) as total_spent'))
                ->whereBetween('jo_date', [$start, $end])
                ->groupBy('customer_name')
                ->get();
        }

        $filename = str_replace(' ', '_', $type) . '.' . strtolower($format);

        if ($format === 'PDF') {
            $view = 'reports.' . str_replace(' ', '_', strtolower($type));
            $pdf = Pdf::loadView($view, compact('data', 'start', 'end'));
            return $pdf->download($filename);
        } elseif ($format === 'Excel') {
            return Excel::download(new SalesExport($data, $type), $filename);
        } elseif ($format === 'CSV') {
            return Excel::download(new SalesExport($data, $type), $filename, \Maatwebsite\Excel\Excel::CSV);
        }
    }
}