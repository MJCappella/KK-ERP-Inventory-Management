<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\Store;
use App\Services\SaleService;
use Exception;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function __construct(protected SaleService $saleService) {}

    public function pos(Request $request)
    {
        $user = $request->user();
        $accessibleStores = Store::accessibleBy($user)->with('branch')->get();

        $selectedStoreId = $request->store_id ?: ($user->store_id ?: $accessibleStores->first()?->id);
        $selectedStore = $accessibleStores->firstWhere('id', $selectedStoreId);

        // Fetch products with their stock at the selected store
        $products = Product::where('is_active', true)
            ->with(['storeStocks' => function ($q) use ($selectedStoreId) {
                if ($selectedStoreId) {
                    $q->where('store_id', $selectedStoreId);
                }
            }])
            ->orderBy('name')
            ->get()
            ->map(function ($product) use ($selectedStoreId) {
                $stockObj = $product->storeStocks->firstWhere('store_id', $selectedStoreId);
                $product->store_stock = $stockObj ? $stockObj->quantity : 0;
                return $product;
            });

        return view('sales.pos', compact('accessibleStores', 'selectedStore', 'products'));
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'store_id' => 'required|exists:stores,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'payment_method' => 'required|string|in:cash,mpesa,card,bank_transfer,credit',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
        ]);

        $store = Store::findOrFail($validated['store_id']);

        if (!$user->canAccessStore($store)) {
            abort(403, 'Unauthorized to record sales for this store.');
        }

        try {
            $sale = $this->saleService->recordSale(
                storeId: $store->id,
                userId: $user->id,
                items: $validated['items'],
                paymentMethod: $validated['payment_method'],
                customerName: $validated['customer_name'] ?? null,
                customerPhone: $validated['customer_phone'] ?? null,
                notes: $validated['notes'] ?? null,
                discountAmount: (float) ($validated['discount_amount'] ?? 0),
                taxAmount: (float) ($validated['tax_amount'] ?? 0)
            );

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Sale recorded successfully!',
                    'sale_id' => $sale->id,
                    'invoice_number' => $sale->invoice_number,
                    'redirect_url' => route('sales.show', $sale),
                ]);
            }

            return redirect()->route('sales.show', $sale)
                ->with('success', "Sale #{$sale->invoice_number} recorded successfully!");
        } catch (Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $accessibleStores = Store::accessibleBy($user)->with('branch')->get();

        $query = Sale::accessibleBy($user)
            ->with(['store.branch', 'user', 'items.product']);

        if ($request->filled('store_id')) {
            $query->where('store_id', $request->store_id);
        }

        if ($request->filled('invoice')) {
            $query->where('invoice_number', 'like', "%{$request->invoice}%");
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $sales = $query->latest()->paginate(15)->withQueryString();

        $totalRevenue = (float) (clone $query)->sum('total_amount');
        $totalSalesCount = (clone $query)->count();

        return view('sales.index', compact('sales', 'accessibleStores', 'totalRevenue', 'totalSalesCount'));
    }

    public function show(Request $request, Sale $sale)
    {
        $user = $request->user();

        if (!$user->canAccessStore($sale->store_id)) {
            abort(403, 'Unauthorized to view this sale.');
        }

        $sale->load(['store.branch', 'user', 'items.product']);

        return view('sales.show', compact('sale'));
    }
}
