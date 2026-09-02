@extends('layouts.app')

@section('title', 'Store Inventory')

@section('content')
    <div class="space-y-6">

        <!-- Page Header  -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="page-title">Store Stocks & Inventory</h1>
                <p class="page-subtitle">Track available on-hand stock quantities, reorder levels, and store valuations.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('inventory.receive.form') }}" class="btn btn-primary shadow-sm shadow-sky-600/30 flex items-center gap-1.5">
                    <i class="fa-solid fa-truck-ramp-box text-xs"></i>
                    <span>Receive Inbound Stock</span>
                </a>
                <a href="{{ route('inventory.adjust.form') }}" class="btn btn-secondary flex items-center gap-1.5">
                    <i class="fa-solid fa-sliders text-xs"></i>
                    <span>Adjust Stock Count</span>
                </a>
            </div>
        </div>

        <!-- Summary KPI Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="stat-card">
                <div>
                    <p class="stat-title">Total Units in Selected Scope</p>
                    <h3 class="stat-value text-slate-900">{{ number_format($totalUnits) }} <span
                            class="text-sm font-medium text-slate-500">units</span></h3>
                </div>
                <div class="stat-icon bg-blue-50 text-sky-600">
                    <i class="fa-solid fa-boxes-stacked text-xl"></i>
                </div>
            </div>

            <div class="stat-card">
                <div>
                    <p class="stat-title">Total Retail Valuation</p>
                    <h3 class="stat-value text-slate-900">KES {{ number_format($totalValuation, 2) }}</h3>
                </div>
                <div class="stat-icon bg-emerald-50 text-emerald-600">
                    <i class="fa-solid fa-money-bill-wave text-xl"></i>
                </div>
            </div>

            <div class="stat-card">
                <div>
                    <p class="stat-title">Low Stock / Out of Stock Alerts</p>
                    <h3 class="stat-value {{ $lowStockCount > 0 ? 'text-amber-600' : 'text-slate-900' }}">
                        {{ $lowStockCount }} <span class="text-sm font-medium text-slate-500">alerts</span></h3>
                </div>
                <div
                    class="stat-icon {{ $lowStockCount > 0 ? 'bg-amber-50 text-amber-600' : 'bg-slate-100 text-slate-600' }}">
                    <i class="fa-solid fa-triangle-exclamation text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Filter Card  -->
        <div class="filter-card">
            <form method="GET" action="{{ route('inventory.index') }}"
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
                <div>
                    <label class="filter-label">Filter by Store</label>
                    <select name="store_id" class="form-select text-xs">
                        <option value="">All Accessible Stores</option>
                        @foreach($accessibleStores as $st)
                            <option value="{{ $st->id }}" {{ $selectedStoreId == $st->id ? 'selected' : '' }}>
                                {{ $st->name }} ({{ $st->branch->name }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="filter-label">Search Product / SKU</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, SKU..."
                        class="form-input text-xs">
                </div>

                <div>
                    <label class="filter-label">Category</label>
                    <select name="category" class="form-select text-xs">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="filter-label">Stock Status</label>
                    <select name="status" class="form-select text-xs">
                        <option value="">All Levels</option>
                        <option value="low" {{ request('status') == 'low' ? 'selected' : '' }}>Low Stock (&le; Reorder Level)
                        </option>
                        <option value="out" {{ request('status') == 'out' ? 'selected' : '' }}>Out of Stock (0 Units)</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-1">Apply</button>
                    <a href="{{ route('inventory.index') }}" class="btn btn-secondary btn-sm">Reset</a>
                </div>
            </form>
        </div>

        <!-- Inventory Table Card -->
        <div class="card">
            <div class="card-header">
                <div>
                    <h2 class="card-title">Inventory Balances ({{ $stocks->total() }})</h2>
                    <p class="text-xs text-slate-500">Instant cached quantities backed by row-locked movement logs.</p>
                </div>
            </div>

            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>SKU / Barcode</th>
                            <th>Store Location</th>
                            <th>Category</th>
                            <th>Selling Price</th>
                            <th>Stock on Hand</th>
                            <th>Reorder Threshold</th>
                            <th>Status</th>
                            <th>Quick Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stocks as $stock)
                            <tr>
                                <td>
                                    <div class="font-bold text-slate-900">{{ $stock->product->name }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $stock->product->unit }}</div>
                                </td>
                                <td>
                                    <div class="font-mono text-slate-700 font-medium text-xs">{{ $stock->product->sku }}</div>
                                    @if($stock->product->barcode)
                                        <div class="font-mono text-[10px] text-slate-400">{{ $stock->product->barcode }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="font-medium text-slate-900">{{ $stock->store->name }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $stock->store->branch->name }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-slate-100 text-slate-700">
                                        {{ $stock->product->category }}
                                    </span>
                                </td>
                                <td class="font-mono font-medium text-slate-800">
                                    KES {{ number_format($stock->product->selling_price, 2) }}
                                </td>
                                <td>
                                    <span
                                        class="text-base font-extrabold {{ $stock->quantity <= 0 ? 'text-rose-600' : ($stock->isLowStock() ? 'text-amber-600' : 'text-slate-900') }}">
                                        {{ $stock->quantity }}
                                    </span>
                                </td>
                                <td class="text-xs text-slate-500">
                                    {{ $stock->product->reorder_level }}
                                </td>
                                <td>
                                    @if($stock->quantity <= 0)
                                        <span class="badge bg-rose-100 text-rose-700 border border-rose-200">Out of Stock</span>
                                    @elseif($stock->isLowStock())
                                        <span class="badge bg-amber-100 text-amber-700 border border-amber-200">Low Stock</span>
                                    @else
                                        <span class="badge bg-emerald-100 text-emerald-700 border border-emerald-200">In
                                            Stock</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex items-center gap-1.5">
                                        <a href="{{ route('inventory.receive.form', ['store_id' => $stock->store_id, 'product_id' => $stock->product_id]) }}"
                                            title="Receive Inbound Delivery" class="btn btn-secondary btn-sm">
                                            + Receive
                                        </a>
                                        <a href="{{ route('inventory.adjust.form', ['store_id' => $stock->store_id, 'product_id' => $stock->product_id]) }}"
                                            title="Adjust Physical Count" class="btn btn-secondary btn-sm">
                                            Adjust
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-10 text-slate-400">
                                    No stock items matching your filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($stocks->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $stocks->links() }}
                </div>
            @endif
        </div>

    </div>
@endsection