@extends('layouts.app')

@section('title', 'Add User Account')

@section('content')
    <div x-data="userForm()" class="max-w-2xl mx-auto space-y-6">

        <div class="flex items-center justify-between">
            <div>
                <h1 class="page-title">Create User Account</h1>
                <p class="page-subtitle">Add a user and configure their operational role and store assignment.</p>
            </div>
            <a href="{{ route('users.index') }}" class="btn btn-primary btn-sm">&larr; Back to Users</a>
        </div>

        <div class="card">
            <form method="POST" action="{{ route('users.store') }}" class="p-6 space-y-4">
                @csrf

                <div>
                    <label for="name" class="filter-label">Full Name <span class="text-sm text-red-500">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required placeholder="e.g. John Doe"
                        class="form-input text-sm">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="email" class="filter-label">Email Address (Login) <span
                                class="text-sm text-red-500">*</span></label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required
                            placeholder="john@kkwholesalers.com" class="form-input text-sm">
                    </div>
                    <div>
                        <label for="phone" class="filter-label">Contact Phone</label>
                        <input type="text" id="phone" name="phone" value="{{ old('phone') }}" placeholder="+254 700 000 000"
                            class="form-input text-sm">
                    </div>
                </div>

                <div>
                    <label for="password" class="filter-label">Password <span class="text-sm text-red-500">*</span></label>
                    <input type="password" id="password" name="password" required placeholder="Minimum 8 characters"
                        class="form-input text-sm">
                </div>

                <div>
                    <label for="role" class="filter-label">System Role <span class="text-sm text-red-500">*</span></label>
                    <select id="role" name="role" x-model="selectedRole" required
                        class="form-select text-sm font-semibold text-sky-900">
                        <option value="admin">Administrator (Full Global Access)</option>
                        <option value="branch_manager">Branch Manager (Scoped to Branch)</option>
                        <option value="store_manager">Store Manager (Scoped to Store Outlet)</option>
                    </select>
                </div>

                <!-- Branch Selector (if Branch Manager) -->
                <div x-show="selectedRole === 'branch_manager'" x-transition>
                    <label for="branch_id" class="filter-label">Assigned Branch <span
                            class="text-sm text-red-500">*</span></label>
                    <select id="branch_id" name="branch_id" class="form-select text-sm">
                        <option value="">-- Select Branch --</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ old('branch_id') == $b->id ? 'selected' : '' }}>
                                {{ $b->name }} ({{ $b->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Store Selector (if Store Manager) -->
                <div x-show="selectedRole === 'store_manager'" x-transition>
                    <label for="store_id" class="filter-label">Assigned Store Outlet <span
                            class="text-sm text-red-500">*</span></label>
                    <select id="store_id" name="store_id" class="form-select text-sm">
                        <option value="">-- Select Store --</option>
                        @foreach($stores as $st)
                            <option value="{{ $st->id }}" {{ old('store_id') == $st->id ? 'selected' : '' }}>
                                {{ $st->name }} ({{ $st->branch->name }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            function userForm() {
                return {
                    selectedRole: "{{ old('role', 'store_manager') }}"
                };
            }
        </script>
    @endpush
@endsection