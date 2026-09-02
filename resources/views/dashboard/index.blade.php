@extends('layouts.app')

@section('title', 'Management Dashboard')

@section('content')
    <div class="space-y-6">

        <!-- Page Header  -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="page-title">Dashboard</h1>
                <p class="page-subtitle">Real-time stock valuation, sales metrics, and auditable inventory ledger.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('sales.pos') }}" class="btn btn-primary shadow-sm shadow-sky-600/30">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    <span>Record New Sale (POS)</span>
                </a>
                <a href="{{ route('transfers.create') }}" class="btn btn-secondary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    <span>Inter-Store Transfer</span>
                </a>
            </div>
        </div>

        <!-- Top KPI Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

            <!-- Total Revenue -->
            <div class="stat-card">
                <div>
                    <p class="stat-title">Total Sales Revenue</p>
                    <h3 class="stat-value text-slate-900">KES {{ number_format($stats['totalRevenue'], 2) }}</h3>
                    <p class="text-xs text-slate-500 mt-1">From <strong
                            class="text-slate-700">{{ $stats['totalSalesCount'] }}</strong> completed orders</p>
                </div>
                <div class="stat-icon bg-blue-50 text-sky-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>

            <!-- Stock Valuation -->
            <div class="stat-card">
                <div>
                    <p class="stat-title">Total Stock Valuation</p>
                    <h3 class="stat-value text-slate-900">KES {{ number_format($stats['totalRetailValue'], 2) }}</h3>
                    <p class="text-xs text-slate-500 mt-1">Cost value: <span class="font-mono text-slate-700">KES
                            {{ number_format($stats['totalCostValue'], 2) }}</span></p>
                </div>
                <div class="stat-icon bg-emerald-50 text-emerald-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
            </div>

            <!-- Available Inventory Units -->
            <div class="stat-card">
                <div>
                    <p class="stat-title">Inventory in Stock</p>
                    <h3 class="stat-value text-slate-900">{{ number_format($stats['totalUnits']) }} <span
                            class="text-sm font-medium text-slate-500">units</span></h3>
                    <p class="text-xs text-slate-500 mt-1">Across <strong
                            class="text-slate-700">{{ $stats['accessibleStoresCount'] }}</strong> active store location(s)
                    </p>
                </div>
                <div class="stat-icon bg-purple-50 text-purple-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
            </div>

            <!-- Reorder Alerts / Transfers -->
            <div class="stat-card">
                <div>
                    <p class="stat-title">Low Stock / Reorders</p>
                    <h3 class="stat-value {{ $stats['lowStockCount'] > 0 ? 'text-amber-600' : 'text-slate-900' }}">
                        {{ $stats['lowStockCount'] }} <span class="text-sm font-medium text-slate-500">items</span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-1"><strong
                            class="text-slate-700">{{ $stats['totalTransfers'] }}</strong> inter-store transfer(s)</p>
                </div>
                <div
                    class="stat-icon {{ $stats['lowStockCount'] > 0 ? 'bg-amber-50 text-amber-600' : 'bg-slate-100 text-slate-600' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
            </div>

        </div>

        <!-- Main Dashboard Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- Left Section (8 cols) -->
            <div class="lg:col-span-8 space-y-6">

                <!-- Store Stock & Sales Performance Table -->
                <div class="card">
                    <div class="card-header">
                        <div>
                            <h2 class="card-title">Store Network Inventory & Sales Performance</h2>
                            <p class="text-xs text-slate-500">Live operational breakdown by store location.</p>
                        </div>
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('branches.index') }}"
                                class="text-xs font-bold text-sky-600 hover:text-sky-700">Manage Branches &rarr;</a>
                        @endif
                    </div>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Store Location</th>
                                    <th>Branch</th>
                                    <th>Stock Units</th>
                                    <th>Stock Valuation</th>
                                    <th>Total Revenue</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stats['storeBreakdown'] as $store)
                                    <tr>
                                        <td>
                                            <div class="font-bold text-slate-900">{{ $store['name'] }}</div>
                                            <div class="text-[11px] font-mono text-slate-500">{{ $store['code'] }}</div>
                                        </td>
                                        <td>
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                                                {{ $store['branch'] }}
                                            </span>
                                        </td>
                                        <td class="font-semibold text-slate-900">{{ number_format($store['units']) }}</td>
                                        <td class="font-mono text-slate-700">KES {{ number_format($store['valuation'], 2) }}
                                        </td>
                                        <td class="font-bold text-sky-700">KES {{ number_format($store['revenue'], 2) }}</td>
                                        <td>
                                            <a href="{{ route('stores.show', $store['id']) }}" class="btn btn-secondary btn-sm">
                                                View Store
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-6 text-slate-400">No stores available for this
                                            account.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Stock Movements Audit Trail -->
                <div class="card">
                    <div class="card-header">
                        <div>
                            <h2 class="card-title">Recent Stock Audit Ledger Movements</h2>
                            <p class="text-xs text-slate-500">Real-time double-entry inventory transactions.</p>
                        </div>
                        <a href="{{ route('movements.index') }}"
                            class="text-xs font-bold text-sky-600 hover:text-sky-700">View Full Ledger &rarr;</a>
                    </div>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Timestamp</th>
                                    <th>Product / SKU</th>
                                    <th>Store</th>
                                    <th>Type</th>
                                    <th>Quantity Change</th>
                                    <th>Balance After</th>
                                    <th>User</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stats['recentMovements'] as $m)
                                    <tr>
                                        <td class="text-xs text-slate-500 font-mono">{{ $m->created_at->format('M d, H:i') }}
                                        </td>
                                        <td>
                                            <div class="font-medium text-slate-900 text-xs">{{ $m->product->name }}</div>
                                            <div class="text-[10px] font-mono text-slate-600">{{ $m->product->sku }}</div>
                                        </td>
                                        <td class="text-xs">{{ $m->store->name }}</td>
                                        <td>
                                            <span class="badge {{ $m->type->badgeClass() }}">
                                                {{ $m->type->shortLabel() }}
                                            </span>
                                        </td>
                                        <td
                                            class="font-bold text-xs {{ $m->quantity > 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                            {{ $m->quantity > 0 ? '+' . $m->quantity : $m->quantity }}
                                        </td>
                                        <td class="font-semibold text-xs text-slate-800">{{ $m->balance_after }}</td>
                                        <td class="text-xs text-slate-500">{{ $m->user?->name ?? 'System' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-6 text-slate-400">No stock movements recorded yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <!-- Right Section (4 cols) -->
            <div class="lg:col-span-4 space-y-6">

                <!-- Low Stock Warnings Card -->
                <div class="card border-amber-200">
                    <div class="card-header bg-amber-50/50">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <h2 class="card-title text-amber-900 text-sm">Low Stock Alerts</h2>
                        </div>
                        <span class="badge bg-amber-100 text-amber-800 border-amber-200">{{ $stats['lowStockCount'] }} Needs
                            Attention</span>
                    </div>
                    <div class="p-4 space-y-3">
                        @forelse($stats['lowStockItems'] as $item)
                            <div
                                class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-200/80">
                                <div>
                                    <h3 class="font-bold text-xs text-slate-900">{{ $item->product->name }}</h3>
                                    <p class="text-[11px] text-slate-500">{{ $item->store->name }} &bull; Reorder:
                                        {{ $item->product->reorder_level }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <span
                                        class="text-xs font-bold px-2 py-0.5 rounded-full {{ $item->quantity <= 0 ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700' }}">
                                        {{ $item->quantity }} Left
                                    </span>
                                    <div class="mt-1">
                                        <a href="{{ route('inventory.receive.form', ['store_id' => $item->store_id, 'product_id' => $item->product_id]) }}"
                                            class="text-[11px] font-bold text-sky-600 hover:text-sky-800">
                                            + Restock
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-slate-400 text-center py-4">All stock levels are above reorder thresholds.
                            </p>
                        @endforelse
                    </div>
                </div>

                <!-- Top Selling Products -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title text-sm">Top Selling SKUs</h2>
                        <span class="text-xs text-slate-400">By units sold</span>
                    </div>
                    <div class="p-4 space-y-3">
                        @forelse($stats['topProducts'] as $index => $top)
                            <div class="flex items-center justify-between p-2.5 rounded-lg hover:bg-slate-50 transition-colors">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-6 h-6 rounded-full bg-sky-100 text-sky-700 font-bold text-xs flex items-center justify-center">
                                        {{ $index + 1 }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-xs text-slate-900">{{ $top->product->name }}</div>
                                        <div class="text-[11px] text-slate-400">{{ $top->product->category }}</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs font-bold text-slate-900">{{ $top->total_qty_sold }} sold</div>
                                    <div class="text-[11px] text-sky-700 font-mono">KES
                                        {{ number_format($top->total_sales_amount, 2) }}
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-slate-400 text-center py-4">No sales recorded yet.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Recent Invoices Widget -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title text-sm">Recent POS Invoices</h2>
                        <a href="{{ route('sales.index') }}" class="text-xs font-bold text-sky-600 hover:text-sky-700">All
                            Sales &rarr;</a>
                    </div>
                    <div class="p-4 space-y-2.5">
                        @forelse($stats['recentSales'] as $sale)
                            <a href="{{ route('sales.show', $sale) }}"
                                class="block p-2.5 rounded-xl border border-slate-100 hover:border-sky-200 hover:bg-sky-50/40 transition-all">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-mono font-bold text-sky-700">{{ $sale->invoice_number }}</span>
                                    <span class="font-bold text-slate-900">KES
                                        {{ number_format($sale->total_amount, 2) }}</span>
                                </div>
                                <div class="flex items-center justify-between text-[11px] text-slate-500 mt-1">
                                    <span>{{ $sale->store->name }}</span>
                                    <span>{{ $sale->created_at->diffForHumans() }}</span>
                                </div>
                            </a>
                        @empty
                            <p class="text-xs text-slate-400 text-center py-4">No recent sales.</p>
                        @endforelse
                    </div>
                </div>

            </div>

        </div>

    </div>
@endsection