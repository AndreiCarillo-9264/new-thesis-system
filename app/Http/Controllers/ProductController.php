<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Product::class, 'product');
    }

    public function index()
    {
        $products = Product::latest()->paginate(15);
        return view('products.index', compact('products'));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_code'  => 'required|string|max:50|unique:products,product_code',
            'product_name'  => 'required|string|max:255',
            'category'      => 'required|string|max:100',
            'unit'          => 'required|string|max:50',
            'is_active'     => 'boolean',
        ]);

        $product = Product::create(array_merge($validated, [
            'is_active' => $request->has('is_active') ? true : false,
        ]));

        ActivityLog::create([
            'user_id'   => Auth::id(),
            'action'    => 'created',
            'module'    => 'products',
            'record_id' => $product->id,
        ]);

        return redirect()->route('products.index')
            ->with('success', 'Product added successfully.');
    }

    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'product_code'  => 'required|string|max:50|unique:products,product_code,' . $product->id,
            'product_name'  => 'required|string|max:255',
            'category'      => 'required|string|max:100',
            'unit'          => 'required|string|max:50',
            'is_active'     => 'boolean',
        ]);

        $product->update(array_merge($validated, [
            'is_active' => $request->has('is_active') ? true : false,
        ]));

        ActivityLog::create([
            'user_id'   => Auth::id(),
            'action'    => 'updated',
            'module'    => 'products',
            'record_id' => $product->id,
        ]);

        return redirect()->route('products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        // Prevent deletion if used in any transaction (strong recommendation)
        if ($product->jobOrders()->exists() ||
            $product->finishedGoods()->exists() ||
            $product->distributions()->exists() ||
            $product->actualInventory()->exists() ||
            $product->inventoryTransfers()->exists()) {
                
            return back()->with('error', 'Cannot delete product that has associated records.');
        }

        $product->delete();

        ActivityLog::create([
            'user_id'   => Auth::id(),
            'action'    => 'deleted',
            'module'    => 'products',
            'record_id' => $product->id,
        ]);

        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }
}