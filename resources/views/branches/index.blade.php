@extends('layouts.app')

@section('title', 'Branches & Stores')

@section('content')
    <div class="space-y-6">

        <!-- Page Header (Britam Style) -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="page-title">Branches & Store Locations</h1>
                <p class="page-subtitle">Manage branches, store outlets and their geographic locations.</p>
            </div>
            @if(auth()->user()->isAdmin())
                <div class="flex items-center gap-2">
                    <a href="{{ route('branches.create') }}" class="btn btn-primary shadow-sm shadow-sky-600/30">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        <span>Add Branch</span>
                    </a>
                    <a href="{{ route('stores.create') }}" class="btn btn-secondary">
                        <span>+ Add Store</span>
                    </a>
                </div>
            @endif
        </div>

        <!-- Branches & Stores Grid -->
        <div class="space-y-6">
            @foreach($branches as $branch)
                <div class="card overflow-hidden border-slate-200">
                    <!-- Branch Banner Header -->
                    <div class="card-header bg-slate-50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 rounded-xl bg-sky-600 text-white font-bold flex items-center justify-center text-sm shadow-sm">
                                {{ substr($branch->code, 3) ?: 'BR' }}
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h2 class="font-extrabold text-base text-slate-900">{{ $branch->name }}</h2>
                                    <span class="badge bg-sky-100 text-sky-800 font-mono text-[10px]">{{ $branch->code }}</span>
                                </div>
                                <p class="text-xs text-slate-500">{{ $branch->location ?: 'No location specified' }} &bull;
                                    {{ $branch->phone ?: 'No contact phone' }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 text-xs">
                            <div class="text-right">
                                <span class="text-slate-400 block text-[10px] uppercase font-bold">Branch Valuation</span>
                                <span class="font-mono font-bold text-slate-900 text-sm">KES
                                    {{ number_format($branch->total_stock_valuation, 2) }}</span>
                            </div>
                            <div class="text-right">
                                <span class="text-slate-400 block text-[10px] uppercase font-bold">Stock Units</span>
                                <span
                                    class="font-bold text-slate-900 text-sm">{{ number_format($branch->total_stock_units) }}</span>
                            </div>
                            @if(auth()->user()->isAdmin())
                                <a href="{{ route('branches.edit', $branch) }}" class="btn btn-secondary btn-sm">
                                    Edit Branch
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- Stores under this Branch -->
                    <div class="p-6">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4">Store Outlets
                            ({{ $branch->stores->count() }})</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @forelse($branch->stores as $store)
                                @php
                                    $storeUnits = $store->stocks->sum('quantity');
                                    $storeValuation = $store->stocks->sum(fn($s) => $s->quantity * ($s->product->selling_price ?? 0));
                                @endphp
                                <div
                                    class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm hover:border-sky-300 hover:shadow-md transition-all flex flex-col justify-between">
                                    <div>
                                        <div class="flex items-center justify-between mb-2">
                                            <span
                                                class="text-[10px] font-bold font-mono px-2 py-0.5 rounded bg-slate-100 text-slate-700">
                                                {{ $store->code }}
                                            </span>
                                            <span class="text-xs font-bold text-slate-900">
                                                {{ number_format($storeUnits) }} units
                                            </span>
                                        </div>
                                        <h4 class="font-bold text-sm text-slate-900">{{ $store->name }}</h4>
                                        <p class="text-xs text-slate-500 mt-0.5">{{ $store->location ?: 'Store Location' }}</p>

                                        <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                                            <span class="text-slate-500">Retail Value:</span>
                                            <span class="font-mono font-bold text-sky-700">KES
                                                {{ number_format($storeValuation, 2) }}</span>
                                        </div>
                                    </div>

                                    <div class="mt-4 flex items-center gap-2">
                                        <a href="{{ route('stores.show', $store) }}" class="btn btn-primary btn-sm flex-1">
                                            View Inventory
                                        </a>
                                        @if(auth()->user()->isAdmin())
                                            <a href="{{ route('stores.edit', $store) }}" class="btn btn-secondary btn-sm">
                                                Edit Store
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <p class="text-xs text-slate-400 col-span-3 py-4 text-center">No stores assigned to this branch yet.
                                </p>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

    </div>
@endsection