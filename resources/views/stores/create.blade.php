@extends('layouts.app')

@section('title', 'Add Store')

@section('content')
    <div class="max-w-2xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="page-title">Add Store Outlet</h1>
                <p class="page-subtitle">Add a retail outlet or warehouse store assigned to a branch.</p>
            </div>
            <a href="{{ route('branches.index') }}" class="btn btn-primary btn-sm">&larr; Back</a>
        </div>

        <div class="card">
            <form method="POST" action="{{ route('stores.store') }}" class="p-6 space-y-4">
                @csrf
                <div>
                    <label for="branch_id" class="filter-label">Parent Branch <span
                            class="text-red-600 text-sm">*</span></label>
                    <select id="branch_id" name="branch_id" required class="form-select text-sm font-medium">
                        <option value="">-- Select Parent Branch --</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ old('branch_id') == $b->id ? 'selected' : '' }}>
                                {{ $b->name }} ({{ $b->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="name" class="filter-label">Store Name <span class="text-red-600 text-sm">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required
                        placeholder="e.g. Mombasa Super Wholesale" class="form-input text-sm">
                </div>

                <div>
                    <label for="code" class="filter-label">Store Code (Unique) <span
                            class="text-red-600 text-sm">*</span></label>
                    <input type="text" id="code" name="code" value="{{ old('code') }}" required
                        placeholder="e.g. STR-MSA-03" class="form-input text-sm font-mono uppercase">
                </div>

                <div>
                    <label for="location" class="filter-label">Location / Physical Address</label>
                    <input type="text" id="location" name="location" value="{{ old('location') }}"
                        placeholder="e.g. Warehouse 3, Port Road" class="form-input text-sm">
                </div>

                <div>
                    <label for="phone" class="filter-label">Contact Phone</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone') }}" placeholder="+254 700 000 000"
                        class="form-input text-sm">
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <a href="{{ route('branches.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Store</button>
                </div>
            </form>
        </div>
    </div>
@endsection