@extends('layouts.app')

@section('title', 'Products Catalog')

@section('content')
    <div class="space-y-6">

        <!-- Page Header  -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="page-title">Products & SKUs</h1>
                <p class="page-subtitle">Manage product master catalog, prices, reorder levels and store inventory
                    distributions.</p>
            </div>
            @if(auth()->user()->isAdmin())
                <div>
                    <a href="{{ route('products.create') }}" class="btn btn-primary shadow-sm shadow-sky-600/30 flex items-center gap-1.5">
                        <i class="fa-solid fa-plus text-xs"></i>
                        <span>Add New Product</span>
                    </a>
                </div>
            @endif
        </div>

        <!-- Filter Card  -->
        <div class="filter-card">
            <form method="GET" action="{{ route('products.index') }}"
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
                <div>
                    <label class="filter-label">Search Product / SKU / Barcode</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or SKU..."
                        class="form-input text-xs">
                </div>

                <div>
                    <label class="filter-label">Filter Category</label>
                    <select name="category" class="form-select text-xs">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="filter-label">Stock Status</label>
                    <select name="stock_status" class="form-select text-xs">
                        <option value="">All Statuses</option>
                        <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>Low Stock Alerts</option>
                        <option value="out" {{ request('stock_status') == 'out' ? 'selected' : '' }}>Out of Stock</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-1">Filter</button>
                    <a href="{{ route('products.index') }}" class="btn btn-secondary btn-sm">Reset</a>
                </div>
            </form>
        </div>

        <!-- Products Table Card -->
        <div class="card">
            <div class="card-header">
                <div>
                    <h2 class="card-title">Product Catalog Items ({{ $products->total() }})</h2>
                    <p class="text-xs text-slate-500">Master product list with multi-store inventory rollup.</p>
                </div>
            </div>

            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>SKU / Barcode</th>
                            <th>Category</th>
                            <th>Cost Price</th>
                            <th>Selling Price</th>
                            <th>Margin</th>
                            <th>Total Stock</th>
                            <th>Stock by Store</th>
                            @if(auth()->user()->isAdmin())
                                <th>Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $p)
                            <tr>
                                <td>
                                    <div class="font-bold text-slate-900">{{ $p->name }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $p->unit }}</div>
                                </td>
                                <td>
                                    <div class="font-mono text-slate-700 font-medium text-xs">{{ $p->sku }}</div>
                                    @if($p->barcode)
                                        <div class="font-mono text-[10px] text-slate-400">{{ $p->barcode }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-slate-100 text-slate-700">
                                        {{ $p->category }}
                                    </span>
                                </td>
                                <td class="font-mono text-slate-600 text-xs">
                                    KES {{ number_format($p->cost_price, 2) }}
                                </td>
                                <td class="font-mono font-bold text-slate-900 text-xs">
                                    KES {{ number_format($p->selling_price, 2) }}
                                </td>
                                <td>
                                    <span class="text-xs font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md">
                                        +{{ $p->margin_percentage }}%
                                    </span>
                                </td>
                                <td>
                                    <span
                                        class="font-bold text-sm {{ $p->total_stock <= $p->reorder_level ? 'text-amber-600' : 'text-slate-900' }}">
                                        {{ $p->total_stock }}
                                    </span>
                                </td>
                                <td>
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($p->storeStocks as $stk)
                                            <span class="text-[10px] px-2 py-0.5 rounded bg-slate-100 font-medium text-slate-700"
                                                title="{{ $stk->store->name }}">
                                                {{ $stk->store->code }}: <strong>{{ $stk->quantity }}</strong>
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                @if(auth()->user()->isAdmin())
                                    <td>
                                        <div class="flex items-center gap-1.5">
                                            <a href="{{ route('products.edit', $p) }}" class="btn btn-secondary btn-sm flex items-center gap-1.5">
                                                <i class="fa-solid fa-pen-to-square text-xs"></i>
                                                <span>Edit</span>
                                            </a>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-10 text-slate-400">
                                    No products found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($products->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $products->links() }}
                </div>
            @endif
        </div>

    </div>
@endsection