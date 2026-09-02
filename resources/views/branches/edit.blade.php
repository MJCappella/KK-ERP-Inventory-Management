@extends('layouts.app')

@section('title', 'Edit Branch ' . $branch->name)

@section('content')
    <div class="max-w-2xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="page-title">Edit Branch</h1>
                <p class="page-subtitle">Update regional branch details.</p>
            </div>
            <a href="{{ route('branches.index') }}" class="btn btn-primary btn-sm flex items-center gap-1.5">
                <i class="fa-solid fa-arrow-left text-xs"></i>
                <span>Back</span>
            </a>
        </div>

        <div class="card">
            <form method="POST" action="{{ route('branches.update', $branch) }}" class="p-6 space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="filter-label">Branch Name <span class="text-red-600 text-sm">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name', $branch->name) }}" required
                        class="form-input text-sm">
                </div>

                <div>
                    <label for="code" class="filter-label">Branch Code <span class="text-red-600 text-sm">*</span></label>
                    <input type="text" id="code" name="code" value="{{ old('code', $branch->code) }}" required
                        class="form-input text-sm font-mono uppercase">
                </div>

                <div>
                    <label for="location" class="filter-label">Location</label>
                    <input type="text" id="location" name="location" value="{{ old('location', $branch->location) }}"
                        class="form-input text-sm">
                </div>

                <div>
                    <label for="phone" class="filter-label">Contact Phone</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone', $branch->phone) }}"
                        class="form-input text-sm">
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <a href="{{ route('branches.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary flex items-center gap-1.5">
                        <i class="fa-solid fa-floppy-disk text-xs"></i>
                        <span>Update Branch</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection