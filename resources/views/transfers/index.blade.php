@extends('layouts.app')

@section('title', 'Inter-Store Transfers')

@section('content')
    <div class="space-y-6">

        <!-- Page Header  -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="page-title">Inter-Store Stock Transfers</h1>
                <p class="page-subtitle">Manage internal transfers, reallocations and stock movements between
                    store outlets.</p>
            </div>
            <div>
                <a href="{{ route('transfers.create') }}" class="btn btn-primary shadow-sm shadow-sky-600/30">
                    <i class="fa-solid fa-plus text-xs"></i>
                    <span>Initiate New Transfer</span>
                </a>
            </div>
        </div>

        <!-- Filter Card  -->
        <div class="filter-card">
            <form method="GET" action="{{ route('transfers.index') }}"
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
                <div>
                    <label class="filter-label">Source Store (Origin)</label>
                    <select name="source_store_id" class="form-select text-xs">
                        <option value="">All Source Stores</option>
                        @foreach($accessibleStores as $st)
                            <option value="{{ $st->id }}" {{ request('source_store_id') == $st->id ? 'selected' : '' }}>
                                {{ $st->name }} ({{ $st->branch->name }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="filter-label">Destination Store</label>
                    <select name="destination_store_id" class="form-select text-xs">
                        <option value="">All Destinations</option>
                        @foreach($accessibleStores as $st)
                            <option value="{{ $st->id }}" {{ request('destination_store_id') == $st->id ? 'selected' : '' }}>
                                {{ $st->name }} ({{ $st->branch->name }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="filter-label">Status</label>
                    <select name="status" class="form-select text-xs">
                        <option value="">All Statuses</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-1">Apply Filters</button>
                    <a href="{{ route('transfers.index') }}" class="btn btn-secondary btn-sm">Reset</a>
                </div>
            </form>
        </div>

        <!-- Transfers Table Card -->
        <div class="card">
            <div class="card-header">
                <div>
                    <h2 class="card-title">Transfer Dispatches ({{ $transfers->total() }})</h2>
                    <p class="text-xs text-slate-500">Atomic inventory transfers between stores.</p>
                </div>
            </div>

            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Transfer #</th>
                            <th>Origin (Source)</th>
                            <th>Destination</th>
                            <th>Total Units</th>
                            <th>Status</th>
                            <th>Initiated By</th>
                            <th>Date Dispatched</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transfers as $trf)
                            <tr>
                                <td>
                                    <a href="{{ route('transfers.show', $trf) }}"
                                        class="font-mono font-bold text-sky-700 hover:underline">
                                        {{ $trf->transfer_number }}
                                    </a>
                                </td>
                                <td>
                                    <div class="font-bold text-slate-900 text-xs">{{ $trf->sourceStore->name }}</div>
                                    <div class="text-[10px] text-slate-500">{{ $trf->sourceStore->branch->name }}</div>
                                </td>
                                <td>
                                    <div class="font-bold text-slate-900 text-xs">{{ $trf->destinationStore->name }}</div>
                                    <div class="text-[10px] text-slate-500">{{ $trf->destinationStore->branch->name }}</div>
                                </td>
                                <td>
                                    <span class="font-bold text-slate-800 text-xs">
                                        {{ $trf->total_quantity }} units
                                    </span>
                                    <span class="text-[10px] text-slate-400 block">({{ $trf->items->count() }} SKU lines)</span>
                                </td>
                                <td>
                                    <span class="badge {{ $trf->status->badgeClass() }}">
                                        {{ $trf->status->label() }}
                                    </span>
                                </td>
                                <td class="text-xs text-slate-600">
                                    {{ $trf->user->name }}
                                </td>
                                <td class="text-xs text-slate-500 font-mono">
                                    {{ $trf->created_at->format('M d, Y H:i') }}
                                </td>
                                <td>
                                    <a href="{{ route('transfers.show', $trf) }}" class="btn btn-secondary btn-sm flex items-center gap-1.5">
                                        <i class="fa-solid fa-file-lines text-xs"></i>
                                        <span>View Manifest</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-10 text-slate-400">
                                    No stock transfers recorded.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($transfers->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $transfers->links() }}
                </div>
            @endif
        </div>

    </div>
@endsection