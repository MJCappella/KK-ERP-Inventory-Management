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
                    class="btn btn-primary shadow-sm shadow-sky-600/30">
                    <span>POS Sale for Store</span>
                </a>
                <a href="{{ route('transfers.create', ['source_store_id' => $store->id]) }}" class="btn btn-secondary">
                    <span>Transfer Out</span>
                </a>
                <a href="{{ route('inventory.receive.form', ['store_id' => $store->id]) }}" class="btn btn-secondary">
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
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
            </div>

            <div class="stat-card">
                <div>
                    <p class="stat-title">Store Inventory Valuation</p>
                    <h3 class="stat-value text-slate-900">KES {{ number_format($totalValuation, 2) }}</h3>
                </div>
                <div class="stat-icon bg-emerald-50 text-emerald-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
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
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
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