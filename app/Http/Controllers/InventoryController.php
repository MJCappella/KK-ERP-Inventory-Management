<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Store;
use App\Models\StoreStock;
use App\Services\InventoryService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InventoryController extends Controller
{
    public function __construct(protected InventoryService $inventoryService) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $accessibleStores = Store::accessibleBy($user)->with('branch')->get();

        $selectedStoreId = $request->store_id ?: ($user->store_id ?: $accessibleStores->first()?->id);

        $query = StoreStock::whereIn('store_id', $accessibleStores->pluck('id'))
            ->with(['store.branch', 'product']);

        if ($selectedStoreId) {
            $query->where('store_id', $selectedStoreId);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $category = $request->category;
            $query->whereHas('product', function ($q) use ($category) {
                $q->where('category', $category);
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'low') {
                $query->whereHas('product', function ($q) {
                    $q->whereRaw('store_stocks.quantity <= products.reorder_level');
                });
            } elseif ($request->status === 'out') {
                $query->where('quantity', '<=', 0);
            }
        }

        $stocks = $query->orderBy('quantity', 'asc')->paginate(15)->withQueryString();
        $categories = Product::distinct()->pluck('category')->filter()->values();

        // Calculate summary metrics for the selected view
        $summaryQuery = StoreStock::whereIn('store_stocks.store_id', $selectedStoreId ? [$selectedStoreId] : $accessibleStores->pluck('id'))
            ->join('products', 'products.id', '=', 'store_stocks.product_id');

        $totalUnits = (int) (clone $summaryQuery)->sum('store_stocks.quantity');
        $totalValuation = (float) (clone $summaryQuery)->sum(\DB::raw('store_stocks.quantity * products.selling_price'));
        $lowStockCount = (clone $summaryQuery)->whereRaw('store_stocks.quantity <= products.reorder_level')->count();

        return view('inventory.index', compact(
            'stocks',
            'accessibleStores',
            'selectedStoreId',
            'categories',
            'totalUnits',
            'totalValuation',
            'lowStockCount'
        ));
    }

    public function receiveForm(Request $request)
    {
        $user = $request->user();
        $stores = Store::accessibleBy($user)->with('branch')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();

        $selectedStoreId = $request->store_id ?: ($user->store_id ?: $stores->first()?->id);
        $selectedProductId = $request->product_id;

        return view('inventory.receive', compact('stores', 'products', 'selectedStoreId', 'selectedProductId'));
    }

    public function receive(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'store_id' => 'required|exists:stores,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'remarks' => 'nullable|string|max:255',
        ]);

        $store = Store::findOrFail($validated['store_id']);

        if (! $user->canAccessStore($store)) {
            Log::channel('security')->warning('[UNAUTHORIZED RECEIVE ATTEMPT]', [
                'user_id' => $user->id,
                'store_id' => $store->id,
                'store_name' => $store->name,
                'user_role' => $user->role->value ?? (string) $user->role,
            ]);
            abort(403, 'Unauthorized to receive stock for this store.');
        }

        try {
            $movement = $this->inventoryService->receiveStock(
                storeId: $store->id,
                productId: (int) $validated['product_id'],
                quantity: (int) $validated['quantity'],
                userId: $user->id,
                remarks: $validated['remarks'] ?: "Inbound stock received at {$store->name}"
            );

            return redirect()->route('inventory.index', ['store_id' => $store->id])
                ->with('success', "Received {$validated['quantity']} units of {$movement->product->name}. New balance: {$movement->balance_after}.");
        } catch (Exception $e) {
            Log::error('[INVENTORY RECEIVE ERROR] '.$e->getMessage(), [
                'user_id' => $user->id,
                'store_id' => $validated['store_id'],
                'product_id' => $validated['product_id'],
                'quantity' => $validated['quantity'],
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function adjustForm(Request $request)
    {
        $user = $request->user();
        $stores = Store::accessibleBy($user)->with('branch')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();

        $selectedStoreId = $request->store_id ?: ($user->store_id ?: $stores->first()?->id);
        $selectedProductId = $request->product_id;

        return view('inventory.adjust', compact('stores', 'products', 'selectedStoreId', 'selectedProductId'));
    }

    public function adjust(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'store_id' => 'required|exists:stores,id',
            'product_id' => 'required|exists:products,id',
            'new_quantity' => 'required|integer|min:0',
            'reason' => 'required|string|max:255',
        ]);

        $store = Store::findOrFail($validated['store_id']);

        if (! $user->canAccessStore($store)) {
            Log::channel('security')->warning('[UNAUTHORIZED ADJUSTMENT ATTEMPT]', [
                'user_id' => $user->id,
                'store_id' => $store->id,
                'store_name' => $store->name,
                'user_role' => $user->role->value ?? (string) $user->role,
            ]);
            abort(403, 'Unauthorized to adjust stock for this store.');
        }

        try {
            $movement = $this->inventoryService->adjustStock(
                storeId: $store->id,
                productId: (int) $validated['product_id'],
                newQuantity: (int) $validated['new_quantity'],
                userId: $user->id,
                reason: $validated['reason']
            );

            return redirect()->route('inventory.index', ['store_id' => $store->id])
                ->with('success', "Stock adjusted for {$movement->product->name}. New balance: {$movement->balance_after}.");
        } catch (Exception $e) {
            Log::error('[INVENTORY ADJUSTMENT ERROR] '.$e->getMessage(), [
                'user_id' => $user->id,
                'store_id' => $validated['store_id'],
                'product_id' => $validated['product_id'],
                'new_quantity' => $validated['new_quantity'],
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * API for real-time stock lookup
     */
    public function getStock(Request $request, Store $store, Product $product)
    {
        $stock = $this->inventoryService->getStoreStock($store->id, $product->id);

        return response()->json([
            'store_id' => $store->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'selling_price' => (float) $product->selling_price,
            'available_stock' => $stock,
            'is_low_stock' => $stock <= $product->reorder_level,
        ]);
    }
}
