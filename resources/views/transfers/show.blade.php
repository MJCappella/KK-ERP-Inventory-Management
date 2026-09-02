@extends('layouts.app')

@section('title', 'Transfer ' . $transfer->transfer_number)

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">

        <!-- Top Action Bar -->
        <div class="flex items-center justify-between no-print">
            <a href="{{ route('transfers.index') }}" class="btn btn-primary btn-sm">
                &larr; Back to Transfers List
            </a>
            <div class="flex items-center gap-2">
                <button type="button" onclick="window.print()" class="btn btn-primary btn-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Print Transfer Manifest</span>
                </button>
                <a href="{{ route('transfers.create') }}" class="btn btn-secondary btn-sm">
                    New Transfer
                </a>
            </div>
        </div>

        <!-- Official Transfer Note Document -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-md p-8 md:p-10">

            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between border-b border-slate-200 pb-8 gap-6">
                <div>
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-lg bg-sky-600 flex items-center justify-center text-white font-extrabold text-lg">
                            KK
                        </div>
                        <div>
                            <h1 class="text-xl font-black text-slate-900 tracking-tight">KK WHOLESALERS</h1>
                            <p class="text-xs text-slate-500 font-semibold">Inter-Store Inventory Transfer Note</p>
                        </div>
                    </div>
                    <div class="text-xs text-slate-500 mt-3 space-y-0.5">
                        <p>Internal Goods Transfer Manifest & Delivery Order</p>
                    </div>
                </div>

                <div class="sm:text-right">
                    <span class="badge {{ $transfer->status->badgeClass() }} mb-2">
                        {{ $transfer->status->label() }}
                    </span>
                    <h2 class="text-2xl font-black font-mono text-slate-900 tracking-tight">{{ $transfer->transfer_number }}
                    </h2>
                    <p class="text-xs text-slate-500 mt-1">Date: <span
                            class="font-mono font-medium text-slate-700">{{ $transfer->created_at->format('M d, Y H:i:s') }}</span>
                    </p>
                    <p class="text-xs text-slate-500">Initiated By: <strong
                            class="text-slate-700">{{ $transfer->user->name }}</strong></p>
                </div>
            </div>

            <!-- Origin vs Destination Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 py-6 border-b border-slate-200 text-xs">

                <div class="bg-slate-50 p-4 rounded-xl border border-slate-200/80">
                    <p class="font-bold uppercase tracking-wider text-slate-400 mb-1 text-[10px]">Origin (Dispatched From)
                    </p>
                    <h3 class="text-sm font-bold text-slate-900">{{ $transfer->sourceStore->name }}</h3>
                    <p class="text-slate-600 font-semibold">{{ $transfer->sourceStore->branch->name }}</p>
                    <p class="text-slate-500 mt-1">{{ $transfer->sourceStore->location ?: 'Branch Warehouse' }}</p>
                </div>

                <div class="bg-sky-50/50 p-4 rounded-xl border border-sky-200/80">
                    <p class="font-bold uppercase tracking-wider text-slate-400 mb-1 text-[10px]">Destination (Delivered To)
                    </p>
                    <h3 class="text-sm font-bold text-slate-900">{{ $transfer->destinationStore->name }}</h3>
                    <p class="text-slate-600 font-semibold">{{ $transfer->destinationStore->branch->name }}</p>
                    <p class="text-slate-500 mt-1">{{ $transfer->destinationStore->location ?: 'Store Outlet' }}</p>
                </div>

            </div>

            <!-- Transfer Manifest Items -->
            <div class="py-6 border-b border-slate-200">
                <h4 class="font-bold text-xs uppercase tracking-wider text-slate-500 mb-3">Transferred Manifest Items</h4>
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 text-slate-400 uppercase tracking-wider text-[11px]">
                            <th class="py-2.5 font-bold">Item Description</th>
                            <th class="py-2.5 font-bold">SKU Code</th>
                            <th class="py-2.5 font-bold">Category</th>
                            <th class="py-2.5 font-bold text-right">Quantity Transferred</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($transfer->items as $item)
                            <tr>
                                <td class="py-3 font-semibold text-slate-900">
                                    {{ $item->product->name }}
                                </td>
                                <td class="py-3 font-mono text-slate-500 text-[11px]">
                                    {{ $item->product->sku }}
                                </td>
                                <td class="py-3 text-slate-600">
                                    {{ $item->product->category }}
                                </td>
                                <td class="py-3 text-right font-mono font-bold text-sm text-slate-900">
                                    {{ $item->quantity }} {{ $item->product->unit }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($transfer->notes)
                <div class="py-4 border-b border-slate-200 text-xs text-slate-600">
                    <strong class="text-slate-700">Dispatch Notes:</strong> {{ $transfer->notes }}
                </div>
            @endif

            <!-- Summary & Ledger Traceability -->
            <div class="pt-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 text-xs">
                <div>
                    <span class="text-slate-500">Total Transferred Units:</span>
                    <strong class="text-base text-slate-900 font-mono ml-2">{{ $transfer->total_quantity }} units</strong>
                </div>

                <div>
                    <a href="{{ route('movements.index', ['search' => $transfer->transfer_number]) }}"
                        class="text-sky-600 hover:underline font-bold">
                        View Stock Ledger Entries &rarr;
                    </a>
                </div>
            </div>

        </div>

    </div>
@endsection