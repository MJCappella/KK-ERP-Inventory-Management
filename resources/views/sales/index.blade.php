@extends('layouts.app')

@section('title', 'Sales & Invoices')

@section('content')
    <div class="space-y-6">

        <!-- Page Hea
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="page-title">Sales Transactions & Invoices</h1>
                <p class="page-subtitle">Track wholesale sales orders, POS receipts, and customer transactions across
                    stores.</p>
            </div>
            <div>
                <a href="{{ route('sales.pos') }}" class="btn btn-primary shadow-sm shadow-sky-600/30">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    <span>+ Record New Sale (POS)</span>
                </a>
            </div>
        </div>

        <!-- Filter Card 
        <div class="filter-card">
            <form method="GET" action="{{ route('sales.index') }}"
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
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
                    <label class="filter-label">Invoice Number</label>
                    <input type="text" name="invoice" value="{{ request('invoice') }}" placeholder="Search INV-..."
                        class="form-input text-xs">
                </div>

                <div>
                    <label class="filter-label">Payment Method</label>
                    <select name="payment_method" class="form-select text-xs">
                        <option value="">All Methods</option>
                        <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="mpesa" {{ request('payment_method') == 'mpesa' ? 'selected' : '' }}>M-Pesa</option>
                        <option value="card" {{ request('payment_method') == 'card' ? 'selected' : '' }}>Card</option>
                        <option value="bank_transfer" {{ request('payment_method') == 'bank_transfer' ? 'selected' : '' }}>
                            Bank Transfer</option>
                        <option value="credit" {{ request('payment_method') == 'credit' ? 'selected' : '' }}>Credit</option>
                    </select>
                </div>

                <div>
                    <label class="filter-label">Date From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-input text-xs">
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-1">
                        Apply Filters
                    </button>
                    <a href="{{ route('sales.index') }}" class="btn btn-secondary btn-sm">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Sales Table Card -->
        <div class="card">
            <div class="card-header">
                <div>
                    <h2 class="card-title">Completed Sales ({{ $sales->total() }})</h2>
                    <p class="text-xs text-slate-500">Total Filtered Revenue: <strong class="text-sky-700 font-mono">KES
                            {{ number_format($totalRevenue, 2) }}</strong></p>
                </div>
            </div>

            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Store / Branch</th>
                            <th>Customer</th>
                            <th>Items Qty</th>
                            <th>Payment</th>
                            <th>Total Amount</th>
                            <th>Date</th>
                            <th>Cashier</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales as $sale)
                            <tr>
                                <td>
                                    <a href="{{ route('sales.show', $sale) }}"
                                        class="font-mono font-bold text-sky-700 hover:underline">
                                        {{ $sale->invoice_number }}
                                    </a>
                                </td>
                                <td>
                                    <div class="font-medium text-slate-900">{{ $sale->store->name }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $sale->store->branch->name }}</div>
                                </td>
                                <td>
                                    <div class="text-slate-800 font-medium">{{ $sale->customer_name ?: 'Walk-in Customer' }}
                                    </div>
                                    @if($sale->customer_phone)
                                        <div class="text-[11px] text-slate-400">{{ $sale->customer_phone }}</div>
                                    @endif
                                </td>
                                <td class="font-semibold text-slate-700">
                                    {{ $sale->items->sum('quantity') }} items
                                </td>
                                <td>
                                    <span class="badge bg-slate-100 text-slate-700 uppercase">
                                        {{ $sale->payment_method }}
                                    </span>
                                </td>
                                <td class="font-bold text-slate-900 font-mono">
                                    KES {{ number_format($sale->total_amount, 2) }}
                                </td>
                                <td class="text-xs text-slate-500 font-mono">
                                    {{ $sale->created_at->format('M d, Y H:i') }}
                                </td>
                                <td class="text-xs text-slate-600">
                                    {{ $sale->user->name }}
                                </td>
                                <td>
                                    <a href="{{ route('sales.show', $sale) }}" class="btn btn-secondary btn-sm">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-3.764 7-7.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        <span>Invoice</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-10 text-slate-400">
                                    No sales matching your filter criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($sales->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $sales->links() }}
                </div>
            @endif
        </div>

    </div>
@endsection