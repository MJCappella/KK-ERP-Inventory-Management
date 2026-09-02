@extends('layouts.app')

@section('title', $store->name)

@section('content')
    <div class="space-y-6">

        <!-- Page Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="page-title">{{ $store->name }}</h1>
                    <span class="badge bg-sky-100 text-sky-800 font-mono">{{ $store->code }}</span>
                </div>
                <p class="page-subtitle">{{ $store->branch->name }} &bull; {{ $store->location ?: 'Store Location' }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('sales.pos', ['store_id' => $store->id]) }}"
                    class="btn btn-primary shadow-sm shadow-sky-600/30 flex items-center gap-1.5">
                    <i class="fa-solid fa-cash-register text-xs"></i>
                    <span>POS Sale for Store</span>
                </a>
                <a href="{{ route('transfers.create', ['source_store_id' => $store->id]) }}" class="btn btn-secondary flex items-center gap-1.5">
                    <i class="fa-solid fa-right-left text-xs"></i>
                    <span>Transfer Out</span>
                </a>
                <a href="{{ route('inventory.receive.form', ['store_id' => $store->id]) }}" class="btn btn-secondary flex items-center gap-1.5">
                    <i class="fa-solid fa-truck-ramp-box text-xs"></i>
                    <span>Receive Stock</span>
                </a>
            </div>
        </div>

        <!-- Store Summary KPI Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="stat-card">
                <div>
                    <p class="stat-title">Store On-Hand Stock</p>
                    <h3 class="stat-value text-slate-900">{{ number_format($totalUnits) }} <span
                            class="text-sm font-medium text-slate-500">units</span></h3>
                </div>
                <div class="stat-icon bg-blue-50 text-sky-600">
                    <i class="fa-solid fa-boxes-stacked text-xl"></i>
                </div>
            </div>

            <div class="stat-card">
                <div>
                    <p class="stat-title">Store Inventory Valuation</p>
                    <h3 class="stat-value text-slate-900">KES {{ number_format($totalValuation, 2) }}</h3>
                </div>
                <div class="stat-icon bg-emerald-50 text-emerald-600">
                    <i class="fa-solid fa-money-bill-wave text-xl"></i>
                </div>
            </div>

            <div class="stat-card">
                <div>
                    <p class="stat-title">Low Stock SKUs</p>
                    <h3 class="stat-value {{ $lowStockCount > 0 ? 'text-amber-600' : 'text-slate-900' }}">
                        {{ $lowStockCount }}
                    </h3>
                </div>
                <div
                    class="stat-icon {{ $lowStockCount > 0 ? 'bg-amber-50 text-amber-600' : 'bg-slate-100 text-slate-600' }}">
                    <i class="fa-solid fa-triangle-exclamation text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Store Inventory Table -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Current Store Stock Levels ({{ $stocks->total() }})</h2>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>SKU</th>
                            <th>Category</th>
                            <th>Unit Price</th>
                            <th>Stock on Hand</th>
                            <th>Reorder Level</th>
                            <th>Status</th>
                            <th>Quick Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stocks as $stock)
                            <tr>
                                <td class="font-bold text-slate-900">{{ $stock->product->name }}</td>
                                <td class="font-mono text-xs text-slate-500">{{ $stock->product->sku }}</td>
                                <td><span class="badge bg-slate-100 text-slate-700">{{ $stock->product->category }}</span></td>
                                <td class="font-mono font-medium text-slate-800">KES
                                    {{ number_format($stock->product->selling_price, 2) }}
                                </td>
                                <td
                                    class="font-bold text-base {{ $stock->quantity <= 0 ? 'text-rose-600' : ($stock->isLowStock() ? 'text-amber-600' : 'text-slate-900') }}">
                                    {{ $stock->quantity }}
                                </td>
                                <td class="text-xs text-slate-500">{{ $stock->product->reorder_level }}</td>
                                <td>
                                    @if($stock->quantity <= 0)
                                        <span class="badge bg-rose-100 text-rose-700">Out of Stock</span>
                                    @elseif($stock->isLowStock())
                                        <span class="badge bg-amber-100 text-amber-700">Low Stock</span>
                                    @else
                                        <span class="badge bg-emerald-100 text-emerald-700">In Stock</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex items-center gap-1">
                                        <a href="{{ route('inventory.receive.form', ['store_id' => $store->id, 'product_id' => $stock->product_id]) }}"
                                            class="btn btn-secondary btn-sm">
                                            + Receive
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-6 text-slate-400">No stock records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($stocks->hasPages())
                <div class="p-4 border-t border-slate-100">{{ $stocks->links() }}</div>
            @endif
        </div>

    </div>
@endsection