@extends('layouts.app')

@section('title', 'Receive Inbound Stock')

@section('content')
    <div class="max-w-2xl mx-auto space-y-6">

        <!-- Page Header  -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="page-title">Receive Inbound Stock</h1>
                <p class="page-subtitle">Record supplier shipments, purchases, or warehouse intake into store inventory.</p>
            </div>
            <a href="{{ route('inventory.index') }}" class="btn btn-secondary btn-sm flex items-center gap-1.5">
                <i class="fa-solid fa-arrow-left text-xs"></i>
                <span>Back to Inventory</span>
            </a>
        </div>

        <!-- Inbound Form Card -->
        <div class="card">
            <div class="card-header bg-emerald-50/50 border-emerald-100">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-truck-ramp-box text-emerald-600 text-base"></i>
                    <h2 class="card-title text-sm text-emerald-900">Inbound Delivery Receipt</h2>
                </div>
                <span class="badge bg-emerald-100 text-emerald-800">Type: INBOUND</span>
            </div>

            <form method="POST" action="{{ route('inventory.receive') }}" class="p-6 space-y-4">
                @csrf

                <div>
                    <label for="store_id" class="filter-label">Destination Store Location *</label>
                    <select id="store_id" name="store_id" required class="form-select text-sm font-semibold">
                        @foreach($stores as $st)
                            <option value="{{ $st->id }}" {{ old('store_id', $selectedStoreId) == $st->id ? 'selected' : '' }}>
                                {{ $st->name }} ({{ $st->branch->name }})
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-slate-500 mt-1">Stock will be credited to this store's on-hand balance.</p>
                </div>

                <div>
                    <label for="product_id" class="filter-label">Product to Receive *</label>
                    <select id="product_id" name="product_id" required class="form-select text-sm">
                        <option value="">-- Select Product --</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" {{ old('product_id', $selectedProductId) == $p->id ? 'selected' : '' }}>
                                {{ $p->name }} (SKU: {{ $p->sku }}) &bull; Unit: {{ $p->unit }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="quantity" class="filter-label">Quantity Received (Units) *</label>
                    <input type="number" id="quantity" name="quantity" value="{{ old('quantity', 1) }}" min="1" required
                        class="form-input text-base font-bold font-mono">
                </div>

                <div>
                    <label for="remarks" class="filter-label">Delivery Notes / Supplier Reference / PO Number</label>
                    <textarea id="remarks" name="remarks" rows="2"
                        placeholder="e.g. Received from Supplier PO-2026-992 via Delivery Truck 1"
                        class="form-textarea text-xs">{{ old('remarks') }}</textarea>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <a href="{{ route('inventory.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-success shadow-md shadow-emerald-600/20 flex items-center gap-1.5">
                        <i class="fa-solid fa-check text-xs"></i>
                        <span>Confirm Receipt & Credit Stock</span>
                    </button>
                </div>
            </form>
        </div>

    </div>
@endsection