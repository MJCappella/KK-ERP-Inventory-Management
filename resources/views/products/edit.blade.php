@extends('layouts.app')

@section('title', 'Edit Product ' . $product->name)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    
    <!-- Page Header  -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="page-title">Edit Product SKU</h1>
            <p class="page-subtitle">Update product pricing, reorder thresholds, and active status.</p>
        </div>
        <a href="{{ route('products.index') }}" class="btn btn-secondary btn-sm">
            &larr; Back to Catalog
        </a>
    </div>

    <!-- Product Form Card -->
    <div class="card">
        <div class="card-header bg-slate-50">
            <h2 class="card-title text-sm">Product Details: {{ $product->name }}</h2>
        </div>

        <form method="POST" action="{{ route('products.update', $product) }}" class="p-6 space-y-4">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="filter-label">Product Name *</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $product->name) }}" required class="form-input text-sm">
                </div>

                <div>
                    <label for="sku" class="filter-label">SKU Code (Unique) *</label>
                    <input type="text" id="sku" name="sku" value="{{ old('sku', $product->sku) }}" required class="form-input text-sm font-mono uppercase">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="barcode" class="filter-label">Barcode / UPC</label>
                    <input type="text" id="barcode" name="barcode" value="{{ old('barcode', $product->barcode) }}" class="form-input text-sm font-mono">
                </div>

                <div>
                    <label for="category" class="filter-label">Category *</label>
                    <input type="text" id="category" name="category" value="{{ old('category', $product->category) }}" required class="form-input text-sm">
                </div>

                <div>
                    <label for="unit" class="filter-label">Unit of Measure *</label>
                    <input type="text" id="unit" name="unit" value="{{ old('unit', $product->unit) }}" required class="form-input text-sm">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="cost_price" class="filter-label">Cost Price (KES) *</label>
                    <input type="number" id="cost_price" name="cost_price" value="{{ old('cost_price', $product->cost_price) }}" step="0.01" min="0" required class="form-input text-sm font-mono font-bold">
                </div>

                <div>
                    <label for="selling_price" class="filter-label">Selling Price (KES) *</label>
                    <input type="number" id="selling_price" name="selling_price" value="{{ old('selling_price', $product->selling_price) }}" step="0.01" min="0" required class="form-input text-sm font-mono font-bold text-sky-700">
                </div>

                <div>
                    <label for="reorder_level" class="filter-label">Reorder Alert Threshold *</label>
                    <input type="number" id="reorder_level" name="reorder_level" value="{{ old('reorder_level', $product->reorder_level) }}" min="0" required class="form-input text-sm font-mono">
                </div>
            </div>

            <div>
                <label for="description" class="filter-label">Description / Packaging Notes</label>
                <textarea id="description" name="description" rows="3" class="form-textarea text-xs">{{ old('description', $product->description) }}</textarea>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                <div>
                    <label class="flex items-center gap-2 text-xs font-semibold text-slate-700 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }} class="rounded text-sky-600">
                        <span>Active Product</span>
                    </label>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('products.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <span>Update Product</span> &rarr;
                    </button>
                </div>
            </div>
        </form>
    </div>

</div>
@endsection
