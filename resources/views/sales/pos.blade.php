@extends('layouts.app')

@section('title', 'Point of Sale (POS)')

@section('content')
    <div x-data="posSystem()" class="space-y-6">

        <!-- Page Header  -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="page-title">Point of Sale (POS) Checkout</h1>
                <p class="page-subtitle">Record wholesale & retail customer sales with atomic concurrency-safe stock
                    deduction.</p>
            </div>

            <!-- Store Selector Filter  -->
            <div class="flex items-center gap-3 bg-white p-2 rounded-xl border border-slate-200 shadow-sm">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 pl-2">Selling Store:</span>
                <select x-model="selectedStoreId" @change="changeStore()"
                    class="form-select text-xs font-bold text-sky-800 bg-sky-50 border-sky-200 rounded-lg py-1.5 px-3">
                    @foreach($accessibleStores as $store)
                        <option value="{{ $store->id }}" {{ $selectedStore?->id === $store->id ? 'selected' : '' }}>
                            {{ $store->name }} ({{ $store->branch->name }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- POS Main Layout (2 Columns) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            <!-- Left Column: Products Catalog & Search (7 Cols) -->
            <div class="lg:col-span-7 space-y-4">

                <!-- Search & Filter Bar -->
                <div class="filter-card">
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                        <div class="sm:col-span-7">
                            <label class="filter-label">Search Products / SKU / Barcode</label>
                            <div class="relative">
                                <input type="text" x-model="searchQuery"
                                    placeholder="Type product name, SKU or scan barcode..." class="form-input pl-9 text-xs">
                                <i class="fa-solid fa-magnifying-glass text-slate-400 absolute left-3 top-3 text-xs"></i>
                            </div>
                        </div>
                        <div class="sm:col-span-5">
                            <label class="filter-label">Filter Category</label>
                            <select x-model="selectedCategory" class="form-select text-xs">
                                <option value="">All Categories</option>
                                <template x-for="cat in categories" :key="cat">
                                    <option :value="cat" x-text="cat"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Product Grid Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-[600px] overflow-y-auto pr-1">
                    <template x-for="p in filteredProducts" :key="p.id">
                        <div class="bg-white border rounded-xl p-4 transition-all flex flex-col justify-between"
                            :class="p.store_stock > 0 ? 'border-slate-200 hover:border-sky-300 hover:shadow-md cursor-pointer' : 'border-slate-200 bg-slate-50 opacity-60'"
                            @click="p.store_stock > 0 ? addToCart(p) : null">
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <span
                                        class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-slate-100 text-slate-600"
                                        x-text="p.category"></span>
                                    <span class="text-xs font-bold px-2 py-0.5 rounded-full"
                                        :class="p.store_stock > 10 ? 'bg-emerald-100 text-emerald-700' : (p.store_stock > 0 ? 'bg-amber-100 text-amber-700' : 'bg-rose-100 text-rose-700')">
                                        <span x-text="p.store_stock"></span> in stock
                                    </span>
                                </div>
                                <h3 class="font-bold text-sm text-slate-900 leading-snug" x-text="p.name"></h3>
                                <p class="text-[11px] font-mono text-slate-500 mt-0.5" x-text="p.sku"></p>
                            </div>

                            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                                <div>
                                    <span class="text-[10px] text-slate-400 block uppercase">Price</span>
                                    <span class="font-bold text-sm text-sky-700">KES <span
                                            x-text="formatNumber(p.selling_price)"></span></span>
                                </div>
                                <button type="button" :disabled="p.store_stock <= 0" class="btn btn-sm"
                                    :class="p.store_stock > 0 ? 'btn-primary' : 'btn-secondary opacity-50 cursor-not-allowed'">
                                    <span x-text="p.store_stock > 0 ? '+ Add' : 'Out of Stock'"></span>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

            </div>

            <!-- Right Column: Cart & Checkout Summary (5 Cols) -->
            <div class="lg:col-span-5">
                <div class="card shadow-lg border-sky-100 sticky top-20">
                    <div class="card-header bg-slate-50 flex items-center justify-between">
                        <div>
                            <h2 class="card-title text-sm flex items-center gap-2">
                                <i class="fa-solid fa-cart-shopping text-sky-600 text-sm"></i>
                                <span>Active Sale Cart (<span x-text="cart.length"></span> items)</span>
                            </h2>
                        </div>
                        <button type="button" @click="clearCart()" x-show="cart.length > 0"
                            class="text-xs text-rose-600 hover:underline">
                            Clear Cart
                        </button>
                    </div>

                    <div class="p-4 space-y-4">

                        <!-- Cart Items List -->
                        <div class="space-y-2.5 max-h-64 overflow-y-auto pr-1">
                            <template x-if="cart.length === 0">
                                <div class="text-center py-8 text-slate-400">
                                    <i class="fa-solid fa-cart-arrow-down text-3xl mx-auto mb-2 text-slate-300 block"></i>
                                    <p class="text-xs">Cart is empty. Click items on the left to add.</p>
                                </div>
                            </template>

                            <template x-for="(item, index) in cart" :key="item.product_id">
                                <div
                                    class="p-2.5 rounded-lg border border-slate-200 bg-white flex items-center justify-between gap-2">
                                    <div class="min-w-0 flex-1">
                                        <h4 class="text-xs font-bold text-slate-900 truncate" x-text="item.name"></h4>
                                        <p class="text-[11px] text-slate-500 font-mono">KES <span
                                                x-text="formatNumber(item.unit_price)"></span> &bull; Stock: <span
                                                x-text="item.max_stock"></span></p>
                                    </div>

                                    <!-- Quantity Controls -->
                                    <div class="flex items-center gap-1.5">
                                        <button type="button" @click="decreaseQty(index)"
                                            class="w-6 h-6 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs flex items-center justify-center">-</button>
                                        <input type="number" x-model.number="item.quantity" @change="validateQty(index)"
                                            min="1" :max="item.max_stock"
                                            class="w-12 text-center text-xs font-bold border border-slate-200 rounded py-0.5">
                                        <button type="button" @click="increaseQty(index)"
                                            class="w-6 h-6 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs flex items-center justify-center">+</button>
                                    </div>

                                    <div class="text-right min-w-[70px]">
                                        <div class="text-xs font-bold text-slate-900">KES <span
                                                x-text="formatNumber(item.unit_price * item.quantity)"></span></div>
                                        <button type="button" @click="removeFromCart(index)"
                                            class="text-[10px] text-rose-500 hover:underline">Remove</button>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Customer & Payment Details -->
                        <form method="POST" action="{{ route('sales.store') }}" @submit="handleSubmit($event)"
                            class="space-y-3 pt-3 border-t border-slate-100">
                            @csrf
                            <input type="hidden" name="store_id" :value="selectedStoreId">

                            <!-- Hidden Cart Items for Form Submission -->
                            <template x-for="(item, idx) in cart" :key="idx">
                                <div>
                                    <input type="hidden" :name="'items[' + idx + '][product_id]'" :value="item.product_id">
                                    <input type="hidden" :name="'items[' + idx + '][quantity]'" :value="item.quantity">
                                    <input type="hidden" :name="'items[' + idx + '][unit_price]'" :value="item.unit_price">
                                </div>
                            </template>

                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="filter-label">Customer Name</label>
                                    <input type="text" name="customer_name" x-model="customerName"
                                        placeholder="Walk-in / Client" class="form-input text-xs">
                                </div>
                                <div>
                                    <label class="filter-label">Phone / Account</label>
                                    <input type="text" name="customer_phone" x-model="customerPhone" placeholder="+254 7..."
                                        class="form-input text-xs">
                                </div>
                            </div>

                            <div>
                                <label class="filter-label">Payment Method</label>
                                <select name="payment_method" x-model="paymentMethod"
                                    class="form-select text-xs font-semibold">
                                    <option value="cash">Cash Payment</option>
                                    <option value="mpesa">M-Pesa Mobile Money</option>
                                    <option value="card">Debit / Credit Card</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="credit">Store Credit / Account</option>
                                </select>
                            </div>

                            <div>
                                <label class="filter-label">Order Notes / Ref</label>
                                <input type="text" name="notes" placeholder="Optional reference notes"
                                    class="form-input text-xs">
                            </div>

                            <!-- Calculations Summary -->
                            <div class="bg-slate-50 p-3 rounded-xl space-y-1.5 text-xs">
                                <div class="flex justify-between text-slate-600">
                                    <span>Subtotal</span>
                                    <span class="font-mono">KES <span x-text="formatNumber(subtotal)"></span></span>
                                </div>
                                <div class="flex justify-between items-center text-slate-600">
                                    <span>Discount Amount (KES)</span>
                                    <input type="number" name="discount_amount" x-model.number="discountAmount" min="0"
                                        step="0.01" class="w-20 text-right px-1.5 py-0.5 border rounded text-xs">
                                </div>
                                <div class="flex justify-between items-center text-slate-600">
                                    <span>Tax / VAT (KES)</span>
                                    <input type="number" name="tax_amount" x-model.number="taxAmount" min="0" step="0.01"
                                        class="w-20 text-right px-1.5 py-0.5 border rounded text-xs">
                                </div>
                                <div
                                    class="pt-2 border-t border-slate-200 flex justify-between items-center font-bold text-sm text-slate-900">
                                    <span>Total Payable</span>
                                    <span class="text-base text-sky-700 font-extrabold font-mono">KES <span
                                            x-text="formatNumber(finalTotal)"></span></span>
                                </div>
                            </div>

                            <!-- Checkout Button -->
                            <button type="submit" :disabled="cart.length === 0 || isProcessing"
                                class="w-full py-3 px-4 rounded-xl bg-sky-600 hover:bg-sky-700 disabled:opacity-50 text-white font-bold text-sm shadow-md shadow-sky-600/30 transition-all flex items-center justify-center gap-2">
                                <i class="fa-solid fa-check text-sm" x-show="!isProcessing"></i>
                                <i class="fa-solid fa-spinner fa-spin text-sm" x-show="isProcessing"></i>
                                <span x-text="isProcessing ? 'Processing Sale...' : 'Complete Sale & Deduct Stock'"></span>
                            </button>
                        </form>

                    </div>
                </div>
            </div>

        </div>

    </div>

    @push('scripts')
        <script>
            function posSystem() {
                const allProducts = @json($products);
                const storeId = "{{ $selectedStore?->id }}";

                return {
                    selectedStoreId: storeId,
                    searchQuery: '',
                    selectedCategory: '',
                    products: allProducts,
                    categories: [...new Set(allProducts.map(p => p.category))],
                    cart: [],
                    customerName: '',
                    customerPhone: '',
                    paymentMethod: 'cash',
                    discountAmount: 0,
                    taxAmount: 0,
                    isProcessing: false,

                    get filteredProducts() {
                        return this.products.filter(p => {
                            const matchesSearch = !this.searchQuery ||
                                p.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                                p.sku.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                                (p.barcode && p.barcode.includes(this.searchQuery));

                            const matchesCat = !this.selectedCategory || p.category === this.selectedCategory;
                            return matchesSearch && matchesCat;
                        });
                    },

                    get subtotal() {
                        return this.cart.reduce((sum, item) => sum + (item.unit_price * item.quantity), 0);
                    },

                    get finalTotal() {
                        return Math.max(0, this.subtotal + (Number(this.taxAmount) || 0) - (Number(this.discountAmount) || 0));
                    },

                    addToCart(product) {
                        const existing = this.cart.find(item => item.product_id === product.id);
                        if (existing) {
                            if (existing.quantity < product.store_stock) {
                                existing.quantity++;
                            } else {
                                toast.warning('Stock Limit Reached', { description: `Only ${product.store_stock} units available in this store.` });
                            }
                        } else {
                            this.cart.push({
                                product_id: product.id,
                                name: product.name,
                                unit_price: parseFloat(product.selling_price),
                                quantity: 1,
                                max_stock: product.store_stock
                            });
                        }
                    },

                    increaseQty(index) {
                        const item = this.cart[index];
                        if (item.quantity < item.max_stock) {
                            item.quantity++;
                        } else {
                            toast.warning('Max Stock Reached', { description: `Only ${item.max_stock} units available in this store.` });
                        }
                    },

                    decreaseQty(index) {
                        const item = this.cart[index];
                        if (item.quantity > 1) {
                            item.quantity--;
                        } else {
                            this.removeFromCart(index);
                        }
                    },

                    validateQty(index) {
                        const item = this.cart[index];
                        if (item.quantity > item.max_stock) {
                            toast.info('Adjusted to Max Stock', { description: `Quantity capped to available stock: ${item.max_stock}` });
                            item.quantity = item.max_stock;
                        }
                        if (item.quantity < 1) {
                            item.quantity = 1;
                        }
                    },

                    removeFromCart(index) {
                        this.cart.splice(index, 1);
                    },

                    clearCart() {
                        if (confirm('Clear all items from the cart?')) {
                            this.cart = [];
                            toast.info('Cart cleared');
                        }
                    },

                    changeStore() {
                        window.location.href = `{{ route('sales.pos') }}?store_id=${this.selectedStoreId}`;
                    },

                    formatNumber(val) {
                        return Number(val || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    },

                    handleSubmit(event) {
                        if (this.cart.length === 0) {
                            event.preventDefault();
                            toast.error('Cart is Empty', { description: 'Please add at least one product before proceeding to checkout.' });
                            return;
                        }
                        this.isProcessing = true;
                    }
                };
            }
        </script>
    @endpush
@endsection