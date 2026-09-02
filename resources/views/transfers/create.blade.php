@extends('layouts.app')

@section('title', 'New Inter-Store Transfer')

@section('content')
    <div x-data="transferForm()" class="max-w-4xl mx-auto space-y-6">

        <!-- Page Header  -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="page-title">Initiate Inter-Store Stock Transfer</h1>
                <p class="page-subtitle">Move items from one store (Origin) to another (Destination)</p>
            </div>
            <a href="{{ route('transfers.index') }}" class="btn btn-primary btn-sm">
                &larr; Back to Transfers
            </a>
        </div>

        <form method="POST" action="{{ route('transfers.store') }}" @submit="handleSubmit($event)" class="space-y-6">
            @csrf

            <!-- Store Selection Card -->
            <div class="card">
                <div class="card-header bg-slate-50">
                    <h2 class="card-title text-sm">1. Select Origin & Destination Locations</h2>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- Source Store -->
                    <div>
                        <label for="source_store_id" class="filter-label">Source Store (Deduct From) <span
                                class="text-red-600 text-sm">*</span></label>
                            <select id="source_store_id" name="source_store_id" x-model="sourceStoreId"
                                @change="onSourceChange()" required
                                class="form-select text-sm font-semibold text-slate-900">
                                <option value="">-- Select Source Store --</option>
                                @foreach($sourceStores as $st)
                                    <option value="{{ $st->id }}" {{ old('source_store_id', $selectedSourceId) == $st->id ? 'selected' : '' }}>
                                        {{ $st->name }} ({{ $st->branch->name }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-[11px] text-slate-500 mt-1">Available inventory will be verified against this
                                store.
                            </p>
                    </div>

                    <!-- Destination Store -->
                    <div>
                        <label for="destination_store_id" class="filter-label">Destination Store (Credit To) <span class="text-red-600 text-sm">*</span></label>
                        <select id="destination_store_id" name="destination_store_id" x-model="destStoreId" required
                            class="form-select text-sm font-semibold text-slate-900">
                            <option value="">-- Select Destination Store --</option>
                            @foreach($allStores as $st)
                                <option value="{{ $st->id }}" :disabled="sourceStoreId == {{ $st->id }}">
                                    {{ $st->name }} ({{ $st->branch->name }})
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-slate-500 mt-1">Cannot be the same as the origin store.</p>
                    </div>

                </div>
            </div>

            <!-- Transfer Manifest Items Card -->
            <div class="card">
                <div class="card-header bg-slate-50 flex items-center justify-between">
                    <h2 class="card-title text-sm">2. Transfer Manifest & Items</h2>
                    <button type="button" @click="addRow()" class="btn btn-secondary btn-sm">
                        + Add Product Line
                    </button>
                </div>

                <div class="p-6 space-y-4">

                    <div class="table-responsive">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="border-b border-slate-200 text-slate-400 uppercase tracking-wider text-[11px]">
                                    <th class="py-2.5 font-bold">Product Item</th>
                                    <th class="py-2.5 font-bold w-32">Source Stock</th>
                                    <th class="py-2.5 font-bold w-32">Transfer Qty</th>
                                    <th class="py-2.5 font-bold w-20 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <template x-for="(row, idx) in rows" :key="idx">
                                    <tr>
                                        <td class="py-3 pr-4">
                                            <select :name="'items[' + idx + '][product_id]'" x-model="row.product_id"
                                                @change="updateRowStock(idx)" required class="form-select text-xs">
                                                <option value="">-- Select Product --</option>
                                                <template x-for="p in products" :key="p.id">
                                                    <option :value="p.id" x-text="p.name + ' (SKU: ' + p.sku + ')'">
                                                    </option>
                                                </template>
                                            </select>
                                        </td>
                                        <td class="py-3">
                                            <span class="font-bold text-xs px-2.5 py-1 rounded-full inline-block"
                                                :class="row.available_stock > 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'">
                                                <span
                                                    x-text="row.available_stock !== null ? row.available_stock + ' avail' : '—'"></span>
                                            </span>
                                        </td>
                                        <td class="py-3">
                                            <input type="number" :name="'items[' + idx + '][quantity]'"
                                                x-model.number="row.quantity" min="1" :max="row.available_stock" required
                                                class="form-input text-xs font-bold text-slate-900 w-28">
                                        </td>
                                        <td class="py-3 text-center">
                                            <button type="button" @click="removeRow(idx)"
                                                class="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div>
                        <label for="notes" class="filter-label">Transfer Dispatch Notes / Reason</label>
                        <textarea id="notes" name="notes" rows="2"
                            placeholder="e.g. Stock replenishment for weekly retail demand."
                            class="form-textarea text-xs">{{ old('notes') }}</textarea>
                    </div>

                </div>

                <div class="card-header bg-slate-50 flex items-center justify-between">
                    <span class="text-xs text-slate-500">
                        Total Lines: <strong x-text="rows.length"></strong> &bull; Total Units: <strong
                            x-text="totalUnits"></strong>
                    </span>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('transfers.index') }}" class="btn btn-secondary btn-sm">Cancel</a>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <span>Dispatch & Transfer</span> &rarr;
                        </button>
                    </div>
                </div>
            </div>

        </form>

    </div>

    @push('scripts')
        <script>
            function transferForm() {
                const allProducts = @json($products);
                const initialSource = "{{ old('source_store_id', $selectedSourceId) }}";

                return {
                    sourceStoreId: initialSource,
                    destStoreId: "{{ old('destination_store_id') }}",
                    products: allProducts,
                    rows: [
                        { product_id: '', quantity: 1, available_stock: null }
                    ],

                    get totalUnits() {
                        return this.rows.reduce((sum, r) => sum + (Number(r.quantity) || 0), 0);
                    },

                    addRow() {
                        this.rows.push({ product_id: '', quantity: 1, available_stock: null });
                    },

                    removeRow(index) {
                        if (this.rows.length > 1) {
                            this.rows.splice(index, 1);
                        } else {
                            alert('Transfer must have at least one product line.');
                        }
                    },

                    onSourceChange() {
                        if (this.sourceStoreId === this.destStoreId) {
                            this.destStoreId = '';
                        }
                        // refresh stock counts for all rows
                        this.rows.forEach((r, idx) => this.updateRowStock(idx));
                    },

                    async updateRowStock(index) {
                        const row = this.rows[index];
                        if (!this.sourceStoreId || !row.product_id) {
                            row.available_stock = null;
                            return;
                        }

                        try {
                            const res = await fetch(`/api/stores/${this.sourceStoreId}/products/${row.product_id}/stock`);
                            if (res.ok) {
                                const data = await res.json();
                                row.available_stock = data.available_stock;
                                if (row.quantity > data.available_stock && data.available_stock > 0) {
                                    row.quantity = data.available_stock;
                                }
                            }
                        } catch (e) {
                            console.error('Error fetching stock:', e);
                        }
                    },

                    handleSubmit(event) {
                        if (this.sourceStoreId === this.destStoreId) {
                            event.preventDefault();
                            alert('Source and destination stores cannot be identical.');
                            return;
                        }

                        for (let i = 0; i < this.rows.length; i++) {
                            const r = this.rows[i];
                            if (!r.product_id) {
                                event.preventDefault();
                                alert('Please select a product for every row.');
                                return;
                            }
                            if (r.available_stock !== null && r.quantity > r.available_stock) {
                                event.preventDefault();
                                alert(`Row ${i + 1}: Requested quantity exceeds available stock (${r.available_stock}).`);
                                return;
                            }
                        }
                    }
                };
            }
        </script>
    @endpush
@endsection