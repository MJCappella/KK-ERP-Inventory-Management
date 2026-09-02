@extends('layouts.app')

@section('title', 'Stock Movements Audit Ledger')

@section('content')
<div class="space-y-6">
    
    <!-- Page Header  -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="page-title">Stock Movements Audit Ledger</h1>
            <p class="page-subtitle">Complete immutable double-entry history of all stock inflows, sales deductions, transfers, and adjustments.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('inventory.receive.form') }}" class="btn btn-secondary btn-sm flex items-center gap-1.5">
                <i class="fa-solid fa-truck-ramp-box text-xs"></i>
                <span>Inbound Receive</span>
            </a>
            <a href="{{ route('inventory.adjust.form') }}" class="btn btn-secondary btn-sm flex items-center gap-1.5">
                <i class="fa-solid fa-sliders text-xs"></i>
                <span>Adjust Count</span>
            </a>
        </div>
    </div>

    <!-- Filter Card  -->
    <div class="filter-card">
        <form method="GET" action="{{ route('movements.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">
            <div>
                <label class="filter-label">Filter by Store</label>
                <select name="store_id" class="form-select text-xs">
                    <option value="">All Accessible Stores</option>
                    @foreach($accessibleStores as $st)
                        <option value="{{ $st->id }}" {{ request('store_id') == $st->id ? 'selected' : '' }}>
                            {{ $st->name }} ({{ $st->branch->name }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="filter-label">Filter Product</label>
                <select name="product_id" class="form-select text-xs">
                    <option value="">All Products</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="filter-label">Movement Type</label>
                <select name="type" class="form-select text-xs">
                    <option value="">All Movement Types</option>
                    @foreach($types as $t)
                        <option value="{{ $t->value }}" {{ request('type') == $t->value ? 'selected' : '' }}>
                            {{ $t->shortLabel() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="filter-label">Date From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-input text-xs">
            </div>

            <div>
                <label class="filter-label">Date To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-input text-xs">
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-1">Filter</button>
                <a href="{{ route('movements.index') }}" class="btn btn-secondary btn-sm">Reset</a>
            </div>
        </form>
    </div>

    <!-- Movement Ledger Table Card -->
    <div class="card">
        <div class="card-header">
            <div>
                <h2 class="card-title">Audit Ledger Entries ({{ $movements->total() }})</h2>
                <p class="text-xs text-slate-500">Chronological immutable inventory log.</p>
            </div>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Store Location</th>
                        <th>Product & SKU</th>
                        <th>Movement Type</th>
                        <th>Delta (+ / -)</th>
                        <th>Balance After</th>
                        <th>Audit Reference</th>
                        <th>Performed By</th>
                        <th>Remarks / Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $m)
                        <tr>
                            <td class="text-xs text-slate-500 font-mono whitespace-nowrap">
                                {{ $m->created_at->format('Y-m-d H:i:s') }}
                            </td>
                            <td>
                                <div class="font-bold text-slate-900 text-xs">{{ $m->store->name }}</div>
                                <div class="text-[10px] text-slate-400">{{ $m->store->branch->name }}</div>
                            </td>
                            <td>
                                <div class="font-semibold text-slate-900 text-xs">{{ $m->product->name }}</div>
                                <div class="text-[10px] font-mono text-slate-500">{{ $m->product->sku }}</div>
                            </td>
                            <td>
                                <span class="badge {{ $m->type->badgeClass() }}">
                                    {{ $m->type->shortLabel() }}
                                </span>
                            </td>
                            <td>
                                <span class="font-extrabold text-sm font-mono {{ $m->quantity > 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                    {{ $m->quantity > 0 ? '+' . $m->quantity : $m->quantity }}
                                </span>
                            </td>
                            <td>
                                <span class="font-bold text-slate-800 text-sm font-mono bg-slate-100 px-2 py-0.5 rounded">
                                    {{ $m->balance_after }}
                                </span>
                            </td>
                            <td>
                                @if($m->reference_type && str_contains($m->reference_type, 'Sale') && $m->reference_id)
                                    <a href="{{ route('sales.show', $m->reference_id) }}" class="text-xs font-mono font-bold text-sky-600 hover:underline">
                                        Sale #{{ $m->reference_id }}
                                    </a>
                                @elseif($m->reference_type && str_contains($m->reference_type, 'Transfer') && $m->reference_id)
                                    <a href="{{ route('transfers.show', $m->reference_id) }}" class="text-xs font-mono font-bold text-sky-600 hover:underline">
                                        Transfer #{{ $m->reference_id }}
                                    </a>
                                @else
                                    <span class="text-[11px] text-slate-400 font-mono">Manual Entry</span>
                                @endif
                            </td>
                            <td class="text-xs text-slate-600">
                                <div class="font-medium">{{ $m->user?->name ?? 'System' }}</div>
                                <div class="text-[10px] text-slate-400">{{ $m->user?->role?->label() }}</div>
                            </td>
                            <td class="text-xs text-slate-600 max-w-xs truncate" title="{{ $m->remarks }}">
                                {{ $m->remarks ?: '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-10 text-slate-400">
                                No stock movements found matching your filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($movements->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $movements->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
