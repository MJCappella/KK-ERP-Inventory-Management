@extends('layouts.app')

@section('title', 'Adjust Stock Count')

@section('content')
    <div class="max-w-2xl mx-auto space-y-6">

        <!-- Page Header  -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="page-title">Manual Stock Count Adjustment</h1>
                <p class="page-subtitle">Reconcile physical inventory counts, write off damaged items, or correct auditing
                    variances.</p>
            </div>
            <a href="{{ route('inventory.index') }}" class="btn btn-secondary btn-sm">
                &larr; Back to Inventory
            </a>
        </div>

        <!-- Adjustment Form Card -->
        <div class="card">
            <div class="card-header bg-purple-50/50 border-purple-100">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                    </svg>
                    <h2 class="card-title text-sm text-purple-900">Inventory Adjustment Log</h2>
                </div>
                <span class="badge bg-purple-100 text-purple-800">Type: ADJUSTMENT</span>
            </div>

            <form method="POST" action="{{ route('inventory.adjust') }}" class="p-6 space-y-4">
                @csrf

                <div>
                    <label for="store_id" class="filter-label">Store Location *</label>
                    <select id="store_id" name="store_id" required class="form-select text-sm font-semibold">
                        @foreach($stores as $st)
                            <option value="{{ $st->id }}" {{ old('store_id', $selectedStoreId) == $st->id ? 'selected' : '' }}>
                                {{ $st->name }} ({{ $st->branch->name }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="product_id" class="filter-label">Product to Adjust *</label>
                    <select id="product_id" name="product_id" required class="form-select text-sm">
                        <option value="">-- Select Product --</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" {{ old('product_id', $selectedProductId) == $p->id ? 'selected' : '' }}>
                                {{ $p->name }} (SKU: {{ $p->sku }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="new_quantity" class="filter-label">Actual Counted Target Quantity (Units) *</label>
                    <input type="number" id="new_quantity" name="new_quantity" value="{{ old('new_quantity', 0) }}" min="0"
                        required class="form-input text-base font-bold font-mono">
                    <p class="text-[11px] text-slate-500 mt-1">The system will calculate the delta (+ or -) and log the
                        adjustment in the audit ledger.</p>
                </div>

                <div>
                    <label for="reason" class="filter-label">Mandatory Audit Reason / Discrepancy Explanation *</label>
                    <textarea id="reason" name="reason" rows="2" required
                        placeholder="e.g. End of month physical stock count reconciliation; 2 bags damaged in transit."
                        class="form-textarea text-xs">{{ old('reason') }}</textarea>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <a href="{{ route('inventory.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary shadow-md shadow-sky-600/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Apply Adjustment & Log Audit</span>
                    </button>
                </div>
            </form>
        </div>

    </div>
@endsection