<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\Store;
use App\Models\StoreStock;
use App\Models\Transfer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardService
{
    /**
     * Gather comprehensive business metrics and KPI cards tailored to the user's role/scope.
     */
    public function getStats(User $user): array
    {
        $startTime = microtime(true);
        $accessibleStoreIds = $user->accessibleStoreIds();

        try {
            // Total Revenue
            $totalRevenue = Sale::whereIn('store_id', $accessibleStoreIds)->sum('total_amount');
            $totalSalesCount = Sale::whereIn('store_id', $accessibleStoreIds)->count();

            // Stock Valuation
            $stockValuation = StoreStock::whereIn('store_stocks.store_id', $accessibleStoreIds)
                ->join('products', 'products.id', '=', 'store_stocks.product_id')
                ->selectRaw('SUM(store_stocks.quantity * products.cost_price) as total_cost_value, SUM(store_stocks.quantity * products.selling_price) as total_retail_value, SUM(store_stocks.quantity) as total_units')
                ->first();

            $totalCostValue = (float) ($stockValuation->total_cost_value ?? 0);
            $totalRetailValue = (float) ($stockValuation->total_retail_value ?? 0);
            $totalUnits = (int) ($stockValuation->total_units ?? 0);

            // Low stock and Out of Stock Alerts
            $lowStockQuery = StoreStock::whereIn('store_stocks.store_id', $accessibleStoreIds)
                ->join('products', 'products.id', '=', 'store_stocks.product_id')
                ->whereRaw('store_stocks.quantity <= products.reorder_level');

            $lowStockCount = (clone $lowStockQuery)->count();
            $outOfStockCount = StoreStock::whereIn('store_id', $accessibleStoreIds)->where('quantity', '<=', 0)->count();

            // Low stock items list
            $lowStockItems = StoreStock::whereIn('store_stocks.store_id', $accessibleStoreIds)
                ->with(['product', 'store.branch'])
                ->join('products', 'products.id', '=', 'store_stocks.product_id')
                ->whereRaw('store_stocks.quantity <= products.reorder_level')
                ->orderBy('store_stocks.quantity', 'asc')
                ->select('store_stocks.*')
                ->limit(6)
                ->get();

            // Transfers Count
            $totalTransfers = Transfer::where(function ($q) use ($accessibleStoreIds) {
                $q->whereIn('source_store_id', $accessibleStoreIds)
                    ->orWhereIn('destination_store_id', $accessibleStoreIds);
            })->count();

            // Recent Movements
            $recentMovements = StockMovement::whereIn('store_id', $accessibleStoreIds)
                ->with(['product', 'store.branch', 'user'])
                ->latest('created_at')
                ->limit(8)
                ->get();

            // Recent Sales
            $recentSales = Sale::whereIn('store_id', $accessibleStoreIds)
                ->with(['store.branch', 'user', 'items.product'])
                ->latest()
                ->limit(6)
                ->get();

            // Store Stock Breakdown
            $storeBreakdown = Store::accessibleBy($user)
                ->with('branch')
                ->withCount(['sales', 'stocks'])
                ->get()
                ->map(function ($store) {
                    $units = (int) StoreStock::where('store_id', $store->id)->sum('quantity');
                    $revenue = (float) Sale::where('store_id', $store->id)->sum('total_amount');
                    $valuation = (float) StoreStock::where('store_id', $store->id)
                        ->join('products', 'products.id', '=', 'store_stocks.product_id')
                        ->sum(DB::raw('store_stocks.quantity * products.selling_price'));

                    return [
                        'id' => $store->id,
                        'name' => $store->name,
                        'branch' => $store->branch->name ?? 'N/A',
                        'code' => $store->code,
                        'units' => $units,
                        'revenue' => $revenue,
                        'valuation' => $valuation,
                        'sales_count' => $store->sales_count,
                    ];
                });

            // Top Selling Products
            $topProducts = SaleItem::whereHas('sale', function ($q) use ($accessibleStoreIds) {
                $q->whereIn('store_id', $accessibleStoreIds);
            })
                ->select('product_id', DB::raw('SUM(quantity) as total_qty_sold'), DB::raw('SUM(total_price) as total_sales_amount'))
                ->groupBy('product_id')
                ->with('product')
                ->orderByDesc('total_qty_sold')
                ->limit(5)
                ->get();

            $stats = [
                'totalRevenue' => $totalRevenue,
                'totalSalesCount' => $totalSalesCount,
                'totalCostValue' => $totalCostValue,
                'totalRetailValue' => $totalRetailValue,
                'totalUnits' => $totalUnits,
                'lowStockCount' => $lowStockCount,
                'outOfStockCount' => $outOfStockCount,
                'totalTransfers' => $totalTransfers,
                'lowStockItems' => $lowStockItems,
                'recentMovements' => $recentMovements,
                'recentSales' => $recentSales,
                'storeBreakdown' => $storeBreakdown,
                'topProducts' => $topProducts,
                'accessibleBranchesCount' => Branch::accessibleBy($user)->count(),
                'accessibleStoresCount' => count($accessibleStoreIds),
                'totalProductsCount' => Product::count(),
            ];

            $durationMs = round((microtime(true) - $startTime) * 1000, 2);
            if ($durationMs > 250) {
                Log::warning('[SLOW DASHBOARD CALCULATION]', [
                    'user_id' => $user->id,
                    'role' => $user->role->value ?? (string) $user->role,
                    'duration_ms' => $durationMs,
                    'accessible_stores_count' => count($accessibleStoreIds),
                ]);
            } else {
                Log::debug('[DASHBOARD STATS CALCULATED]', [
                    'user_id' => $user->id,
                    'duration_ms' => $durationMs,
                ]);
            }

            return $stats;
        } catch (\Throwable $e) {
            Log::error('[DASHBOARD STATS FAILED]', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            throw $e;
        }
    }
}
