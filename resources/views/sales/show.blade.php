@extends('layouts.app')

@section('title', 'Invoice ' . $sale->invoice_number)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    
    <!-- Top Action Bar -->
    <div class="flex items-center justify-between no-print">
        <a href="{{ route('sales.index') }}" class="btn btn-secondary btn-sm">
            &larr; Back to Sales History
        </a>
        <div class="flex items-center gap-2">
            <button type="button" onclick="window.print()" class="btn btn-primary btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>Print Invoice / Receipt</span>
            </button>
            <a href="{{ route('sales.pos') }}" class="btn btn-secondary btn-sm">
                + New Sale
            </a>
        </div>
    </div>

    <!-- Official Printable Invoice Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-md p-8 md:p-10">
        
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between border-b border-slate-200 pb-8 gap-6">
            <div>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-sky-600 flex items-center justify-center text-white font-extrabold text-lg">
                        KK
                    </div>
                    <div>
                        <h1 class="text-xl font-black text-slate-900 tracking-tight">KK WHOLESALERS</h1>
                        <p class="text-xs text-slate-500 font-semibold">{{ $sale->store->name }} &bull; {{ $sale->store->branch->name }}</p>
                    </div>
                </div>
                <div class="text-xs text-slate-500 mt-3 space-y-0.5">
                    <p>{{ $sale->store->location ?: 'Commercial District' }}</p>
                    <p>Phone: {{ $sale->store->phone ?: '+254 700 000 000' }}</p>
                </div>
            </div>

            <div class="sm:text-right">
                <span class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800 mb-2">
                    Paid &bull; {{ strtoupper($sale->payment_method) }}
                </span>
                <h2 class="text-2xl font-black font-mono text-slate-900 tracking-tight">{{ $sale->invoice_number }}</h2>
                <p class="text-xs text-slate-500 mt-1">Date: <span class="font-mono font-medium text-slate-700">{{ $sale->created_at->format('M d, Y H:i:s') }}</span></p>
                <p class="text-xs text-slate-500">Cashier: <strong class="text-slate-700">{{ $sale->user->name }}</strong></p>
            </div>
        </div>

        <!-- Customer & Order Meta -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 py-6 border-b border-slate-200 text-xs">
            <div>
                <p class="font-bold uppercase tracking-wider text-slate-400 mb-1 text-[11px]">Billed To (Customer)</p>
                <h3 class="text-sm font-bold text-slate-900">{{ $sale->customer_name ?: 'Walk-in Cash Customer' }}</h3>
                @if($sale->customer_phone)
                    <p class="text-slate-500 mt-0.5 font-mono">{{ $sale->customer_phone }}</p>
                @endif
            </div>
            <div class="sm:text-right">
                <p class="font-bold uppercase tracking-wider text-slate-400 mb-1 text-[11px]">Payment Reference</p>
                <p class="text-slate-800 font-medium capitalize">Payment Method: {{ $sale->payment_method }}</p>
                @if($sale->notes)
                    <p class="text-slate-500 mt-1 italic">"{{ $sale->notes }}"</p>
                @endif
            </div>
        </div>

        <!-- Line Items Table -->
        <div class="py-6 border-b border-slate-200">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-400 uppercase tracking-wider text-[11px]">
                        <th class="py-2.5 font-bold">Item & Description</th>
                        <th class="py-2.5 font-bold">SKU</th>
                        <th class="py-2.5 font-bold text-center">Qty</th>
                        <th class="py-2.5 font-bold text-right">Unit Price</th>
                        <th class="py-2.5 font-bold text-right">Total (KES)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($sale->items as $item)
                        <tr>
                            <td class="py-3 font-semibold text-slate-900">
                                {{ $item->product->name }}
                            </td>
                            <td class="py-3 font-mono text-slate-500 text-[11px]">
                                {{ $item->product->sku }}
                            </td>
                            <td class="py-3 text-center font-bold text-slate-800">
                                {{ $item->quantity }}
                            </td>
                            <td class="py-3 text-right font-mono text-slate-700">
                                {{ number_format($item->unit_price, 2) }}
                            </td>
                            <td class="py-3 text-right font-mono font-bold text-slate-900">
                                {{ number_format($item->total_price, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Financial Summary -->
        <div class="py-6 flex justify-end">
            <div class="w-full sm:w-72 space-y-2 text-xs">
                <div class="flex justify-between text-slate-600">
                    <span>Subtotal</span>
                    <span class="font-mono">KES {{ number_format($sale->subtotal, 2) }}</span>
                </div>
                @if($sale->discount_amount > 0)
                    <div class="flex justify-between text-emerald-700">
                        <span>Discount</span>
                        <span class="font-mono">- KES {{ number_format($sale->discount_amount, 2) }}</span>
                    </div>
                @endif
                @if($sale->tax_amount > 0)
                    <div class="flex justify-between text-slate-600">
                        <span>Tax / VAT</span>
                        <span class="font-mono">+ KES {{ number_format($sale->tax_amount, 2) }}</span>
                    </div>
                @endif
                <div class="pt-2 border-t border-slate-200 flex justify-between font-extrabold text-base text-slate-900">
                    <span>Total Amount</span>
                    <span class="text-sky-700 font-mono">KES {{ number_format($sale->total_amount, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Stock Ledger Traceability Footer -->
        <div class="bg-slate-50 rounded-xl p-4 border border-slate-200/80 text-[11px] text-slate-500 flex flex-col sm:flex-row items-center justify-between gap-2">
            <div>
                <strong class="text-slate-700">Stock Ledger Audit Status:</strong> Automatically recorded in <code class="font-mono text-sky-700">stock_movements</code> with row-level locks.
            </div>
            <div>
                <a href="{{ route('movements.index', ['search' => $sale->invoice_number]) }}" class="text-sky-600 hover:underline font-bold">
                    View Stock Ledger Movement &rarr;
                </a>
            </div>
        </div>

    </div>

</div>
@endsection
