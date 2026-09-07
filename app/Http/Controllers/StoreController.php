<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StoreController extends Controller
{
    // get store details
    public function show(Request $request, Store $store)
    {
        $user = $request->user();

        if (! $user->canAccessStore($store)) {
            abort(403, 'Unauthorized access to this store.');
        }

        $store->load(['branch', 'users']);

        $stocksQuery = StoreStock::where('store_id', $store->id)
            ->with('product');

        if ($request->filled('search')) {
            $search = $request->search;
            $stocksQuery->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'low') {
                $stocksQuery->whereHas('product', function ($q) {
                    $q->whereRaw('store_stocks.quantity <= products.reorder_level');
                });
            } elseif ($request->status === 'out') {
                $stocksQuery->where('quantity', '<=', 0);
            }
        }

        $stocks = $stocksQuery->paginate(15)->withQueryString();

        $totalUnits = (int) StoreStock::where('store_id', $store->id)->sum('quantity');
        $totalValuation = (float) StoreStock::where('store_id', $store->id)
            ->join('products', 'products.id', '=', 'store_stocks.product_id')
            ->sum(\DB::raw('store_stocks.quantity * products.selling_price'));

        $lowStockCount = StoreStock::where('store_id', $store->id)
            ->join('products', 'products.id', '=', 'store_stocks.product_id')
            ->whereRaw('store_stocks.quantity <= products.reorder_level')
            ->count();

        $recentSales = $store->sales()->with(['user', 'items.product'])->latest()->limit(5)->get();
        $recentMovements = $store->stockMovements()->with(['product', 'user'])->latest('created_at')->limit(5)->get();

        return view('stores.show', compact(
            'store',
            'stocks',
            'totalUnits',
            'totalValuation',
            'lowStockCount',
            'recentSales',
            'recentMovements'
        ));
    }

    // create store
    public function create()
    {
        if (! auth()->user()->isAdmin()) {
            Log::channel('security')->warning('[UNAUTHORIZED STORE CREATION ATTEMPT]', [
                'user_id' => auth()->id(),
                'role' => auth()->user()->role->value ?? (string) auth()->user()->role,
            ]);
            abort(403, 'Only administrators can create stores.');
        }

        $branches = Branch::all();

        return view('stores.create', compact('branches'));
    }

    // store store
    public function store(Request $request)
    {
        if (! auth()->user()->isAdmin()) {
            Log::channel('security')->warning('[UNAUTHORIZED STORE CREATION ATTEMPT]', [
                'user_id' => auth()->id(),
                'role' => auth()->user()->role->value ?? (string) auth()->user()->role,
            ]);
            abort(403, 'Only administrators can create stores.');
        }

        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:stores,code',
            'location' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        $store = Store::create($validated);

        // Initialize stock rows for all existing products
        $products = Product::all();
        foreach ($products as $product) {
            StoreStock::firstOrCreate(
                ['store_id' => $store->id, 'product_id' => $product->id],
                ['quantity' => 0]
            );
        }

        Log::channel('operations')->info('[STORE CREATED]', [
            'store_id' => $store->id,
            'name' => $store->name,
            'code' => $store->code,
            'branch_id' => $store->branch_id,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('branches.index')->with('success', "Store '{$store->name}' created successfully.");
    }

    // edit store
    public function edit(Store $store)
    {
        if (! auth()->user()->isAdmin()) {
            Log::channel('security')->warning('[UNAUTHORIZED STORE EDIT ATTEMPT]', [
                'user_id' => auth()->id(),
                'store_id' => $store->id,
                'role' => auth()->user()->role->value ?? (string) auth()->user()->role,
            ]);
            abort(403, 'Only administrators can edit stores.');
        }

        $branches = Branch::all();

        return view('stores.edit', compact('store', 'branches'));
    }

    // update store
    public function update(Request $request, Store $store)
    {
        if (! auth()->user()->isAdmin()) {
            Log::channel('security')->warning('[UNAUTHORIZED STORE UPDATE ATTEMPT]', [
                'user_id' => auth()->id(),
                'store_id' => $store->id,
                'role' => auth()->user()->role->value ?? (string) auth()->user()->role,
            ]);
            abort(403, 'Only administrators can edit stores.');
        }

        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:stores,code,'.$store->id,
            'location' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        $store->update($validated);

        Log::channel('operations')->info('[STORE UPDATED]', [
            'store_id' => $store->id,
            'name' => $store->name,
            'code' => $store->code,
            'updated_by' => auth()->id(),
            'changes' => array_keys($validated),
        ]);

        return redirect()->route('stores.show', $store)->with('success', "Store '{$store->name}' updated successfully.");
    }
}
