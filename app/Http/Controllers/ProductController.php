<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Store;
use App\Models\StoreStock;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['storeStocks.store']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'low') {
                $query->whereHas('storeStocks', function ($q) {
                    $q->whereRaw('store_stocks.quantity <= products.reorder_level');
                });
            } elseif ($request->stock_status === 'out') {
                $query->whereHas('storeStocks', function ($q) {
                    $q->where('store_stocks.quantity', '<=', 0);
                });
            }
        }

        $products = $query->orderBy('name')->paginate(15)->withQueryString();
        $categories = Product::distinct()->pluck('category')->filter()->values();
        $stores = Store::accessibleBy($request->user())->get();

        return view('products.index', compact('products', 'categories', 'stores'));
    }

    public function create()
    {
        $this->authorizeAdmin();
        $categories = Product::distinct()->pluck('category')->filter()->values();

        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:100|unique:products,sku',
            'barcode' => 'nullable|string|max:100',
            'category' => 'required|string|max:100',
            'unit' => 'required|string|max:50',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'reorder_level' => 'required|integer|min:0',
            'description' => 'nullable|string',
        ]);

        $product = Product::create($validated);

        // Initialize store stock records for all stores with 0 if not present
        $stores = Store::all();
        foreach ($stores as $store) {
            StoreStock::firstOrCreate(
                ['store_id' => $store->id, 'product_id' => $product->id],
                ['quantity' => 0]
            );
        }

        return redirect()->route('products.index')->with('success', "Product '{$product->name}' created successfully.");
    }

    public function edit(Product $product)
    {
        $this->authorizeAdmin();
        $categories = Product::distinct()->pluck('category')->filter()->values();

        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:100|unique:products,sku,' . $product->id,
            'barcode' => 'nullable|string|max:100',
            'category' => 'required|string|max:100',
            'unit' => 'required|string|max:50',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'reorder_level' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $product->update($validated);

        return redirect()->route('products.index')->with('success', "Product '{$product->name}' updated successfully.");
    }

    public function destroy(Product $product)
    {
        $this->authorizeAdmin();

        if ($product->saleItems()->exists() || $product->stockMovements()->exists()) {
            return back()->with('error', "Cannot delete product '{$product->name}' because historical transactions/movements exist. You can disable it instead.");
        }

        $product->delete();
        return redirect()->route('products.index')->with('success', "Product deleted successfully.");
    }

    protected function authorizeAdmin(): void
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only administrators can manage products.');
        }
    }
}
