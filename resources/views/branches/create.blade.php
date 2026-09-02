@extends('layouts.app')

@section('title', 'Add Branch')

@section('content')
    <div class="max-w-2xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="page-title">Create New Branch</h1>
                <p class="page-subtitle">Add a branch to group wholesale and retail store operations.</p>
            </div>
            <a href="{{ route('branches.index') }}" class="btn btn-primary btn-sm">&larr; Back</a>
        </div>

        <div class="card">
            <form method="POST" action="{{ route('branches.store') }}" class="p-6 space-y-4">
                @csrf
                <div>
                    <label for="name" class="filter-label">Branch Name <span class="text-red-600 text-sm">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required
                        placeholder="e.g. Branch 3 - Kisumu Hub" class="form-input text-sm">
                </div>

                <div>
                    <label for="code" class="filter-label">Branch Code (Unique) <span
                            class="text-red-600 text-sm">*</span></label>
                    <input type="text" id="code" name="code" value="{{ old('code') }}" required placeholder="e.g. BR-KSM"
                        class="form-input text-sm font-mono uppercase">
                </div>

                <div>
                    <label for="location" class="filter-label">Location / Address</label>
                    <input type="text" id="location" name="location" value="{{ old('location') }}"
                        placeholder="e.g. Lake Basin Mall, Kisumu" class="form-input text-sm">
                </div>

                <div>
                    <label for="phone" class="filter-label">Contact Phone</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone') }}" placeholder="+254 700 000 000"
                        class="form-input text-sm">
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <a href="{{ route('branches.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Branch</button>
                </div>
            </form>
        </div>
    </div>
@endsection