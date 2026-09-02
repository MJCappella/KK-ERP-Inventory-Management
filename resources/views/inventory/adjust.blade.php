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
            <a href="{{ route('inventory.index') }}" class="btn btn-secondary btn-sm flex items-center gap-1.5">
                <i class="fa-solid fa-arrow-left text-xs"></i>
                <span>Back to Inventory</span>
            </a>
        </div>

        <!-- Adjustment Form Card -->
        <div class="card">
            <div class="card-header bg-purple-50/50 border-purple-100">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-sliders text-purple-600 text-base"></i>
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
                    <button type="submit" class="btn btn-primary shadow-md shadow-sky-600/20 flex items-center gap-1.5">
                        <i class="fa-solid fa-check text-xs"></i>
                        <span>Apply Adjustment & Log Audit</span>
                    </button>
                </div>
            </form>
        </div>

    </div>
@endsection