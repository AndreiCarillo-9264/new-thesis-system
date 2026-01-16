<?php
// Updated: app/Http/Controllers/DashboardController.php (only sales() method updated, others unchanged)
namespace App\Http\Controllers;

use App\Models\ActualInventory;
use App\Models\Distribution;
use App\Models\FinishedGood;
use App\Models\InventoryAudit;
use App\Models\InventoryTransfer;
use App\Models\JobOrder;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Main Dashboard - Overview for all users
     */
    public function main()
    {
        $totalJobOrders     = JobOrder::count();
        $totalProduced      = FinishedGood::sum('quantity_produced');
        $totalDistributed   = Distribution::sum('quantity_distributed');
        $currentInventory   = ActualInventory::sum('actual_quantity');

        // Monthly production for last 12 months (chart data)
        $monthlyProduction = FinishedGood::selectRaw('DATE_FORMAT(production_date, "%Y-%m") as month, SUM(quantity_produced) as total')
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        $recentJobOrders     = JobOrder::with('product')->latest()->limit(5)->get();
        $recentFG            = FinishedGood::with(['jobOrder.product'])->latest()->limit(5)->get();
        $recentDistributions = Distribution::with(['jobOrder.product'])->latest()->limit(5)->get();
        $lowStock            = ActualInventory::with('product')
            ->where('actual_quantity', '<', 50)
            ->get();

        return view('dashboards.main', compact(
            'totalJobOrders',
            'totalProduced',
            'totalDistributed',
            'currentInventory',
            'monthlyProduction',
            'recentJobOrders',
            'recentFG',
            'recentDistributions',
            'lowStock'
        ));
    }

    /**
     * Sales Dashboard - Job Orders & Distributions overview
     */
    public function sales()
    {
        $revenue = JobOrder::sum(\DB::raw('ordered_quantity * unit_price'));
        $pendingOrders = JobOrder::whereIn('status', ['open', 'in_progress'])->count();
        $completedOrders = JobOrder::where('status', 'completed')->count();

        $completedJobs = JobOrder::where('status', 'completed')->with('distributions')->get();
        $onTimeCount = $completedJobs->filter(function ($jo) {
            $lastDelivery = $jo->distributions->max('distribution_date');
            return $lastDelivery && Carbon::parse($lastDelivery) <= $jo->due_date;
        })->count();
        $onTimePercentage = $completedJobs->count() > 0 ? round(($onTimeCount / $completedJobs->count()) * 100) : 0;

        $products = Product::where('is_active', true)->get();
        $users = User::all();

        return view('dashboards.sales', compact(
            'revenue',
            'pendingOrders',
            'completedOrders',
            'onTimePercentage',
            'products',
            'users'
        ));
    }

    /**
     * NEW: Sales Report - Detailed view/export of sales data
     */
    public function salesReport()
    {
        // Core stats
        $totalJobOrders     = JobOrder::count();
        $completedJobOrders = JobOrder::where('status', 'completed')->count();
        $totalDistributed   = Distribution::sum('quantity_distributed');
        $monthlyDistributed = Distribution::whereMonth('distribution_date', now()->month)
            ->whereYear('distribution_date', now()->year)
            ->sum('quantity_distributed');

        // Per-product summary (useful for report)
        $productSummary = Distribution::selectRaw('product_id, SUM(quantity_distributed) as total_distributed')
            ->with('product')
            ->groupBy('product_id')
            ->orderByDesc('total_distributed')
            ->limit(10)
            ->get();

        // Recent distributions for detailed view
        $recentDistributions = Distribution::with(['jobOrder.product'])
            ->latest()
            ->limit(20)
            ->get();

        return view('reports.sales', compact(
            'totalJobOrders',
            'completedJobOrders',
            'totalDistributed',
            'monthlyDistributed',
            'productSummary',
            'recentDistributions'
        ));
    }

    /**
     * Production Dashboard
     */
    public function production()
    {
        $pendingProduction = JobOrder::whereIn('status', ['open', 'in_progress'])->count();
        $producedToday = FinishedGood::whereDate('production_date', today())->sum('quantity_produced');
        $completionJobs = JobOrder::where('status', 'completed')->get();
        $completionPercentage = $completionJobs->count() > 0 ? round(($completionJobs->filter(function ($jo) {
            return $jo->finishedGoods->sum('quantity_produced') >= $jo->ordered_quantity;
        })->count() / $completionJobs->count()) * 100) : 0;
        $backlog = JobOrder::whereIn('status', ['open', 'in_progress'])->sum(\DB::raw('ordered_quantity - (SELECT COALESCE(SUM(quantity_produced), 0) FROM finished_goods WHERE finished_goods.job_order_id = job_orders.id)'));

        $products = Product::where('is_active', true)->get();

        return view('dashboards.production', compact(
            'pendingProduction',
            'producedToday',
            'completionPercentage',
            'backlog',
            'products'
        ));
    }
    
    /**
     * Inventory Dashboard
     */
    public function inventory()
    {
        $stockOnHand = ActualInventory::sum('actual_quantity');
        $lowStockCount = ActualInventory::whereColumn('actual_quantity', '<', 'min_stock')->count();
        $stockInToday = InventoryAudit::where('adjustment_type', 'add')
            ->whereDate('created_at', today())
            ->sum('quantity');
        $stockOutToday = InventoryAudit::where('adjustment_type', 'remove')
            ->whereDate('created_at', today())
            ->sum('quantity');

        $inventories = ActualInventory::with('product')->get();
        $transfers = InventoryTransfer::with('product')->latest()->limit(10)->get();

        return view('dashboards.inventory', compact(
            'stockOnHand',
            'lowStockCount',
            'stockInToday',
            'stockOutToday',
            'inventories',
            'transfers'
        ));
    }

    /**
     * Logistics Dashboard
     */
    public function logistics()
    {
        $deliveriesToday  = Distribution::whereDate('distribution_date', today())->count();
        $pendingDispatch  = JobOrder::whereIn('status', ['open', 'in_progress'])->count();
        $transfersToday   = InventoryTransfer::whereDate('transfer_date', today())->count();

        $distributions = Distribution::with(['jobOrder.product'])->latest()->paginate(15);
        $transfers     = InventoryTransfer::with('product')->latest()->paginate(15);

        return view('dashboards.logistics', compact(
            'deliveriesToday',
            'pendingDispatch',
            'transfersToday',
            'distributions',
            'transfers'
        ));
    }
}