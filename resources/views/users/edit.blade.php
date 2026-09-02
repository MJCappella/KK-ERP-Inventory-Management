@extends('layouts.app')

@section('title', 'Edit User ' . $user->name)

@section('content')
    <style>
        .reqStar {
            color: #ef4444;
            font-size: small;
        }
    </style>
    <div x-data="userEditForm()" class="max-w-2xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="page-title">Edit User Account</h1>
                <p class="page-subtitle">Update role assignment, operational scope or reset password.</p>
            </div>
            <a href="{{ route('users.index') }}" class="btn btn-primary btn-sm flex items-center gap-1.5">
                <i class="fa-solid fa-arrow-left text-xs"></i>
                <span>Back to Users</span>
            </a>
        </div>

        <div class="card">
            <form method="POST" action="{{ route('users.update', $user) }}" class="p-6 space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="filter-label">Full Name <span class="reqStar">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                        class="form-input text-sm">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="email" class="filter-label">Email Address (Login) <span class="reqStar">*</span></label>
                        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                            class="form-input text-sm">
                    </div>
                    <div>
                        <label for="phone" class="filter-label">Contact Phone</label>
                        <input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}"
                            class="form-input text-sm">
                    </div>
                </div>

                <div>
                    <label for="password" class="filter-label">New Password (Leave blank to leave unchanged)</label>
                    <input type="password" id="password" name="password" placeholder="Enter new password if changing..."
                        class="form-input text-sm">
                </div>

                <div>
                    <label for="role" class="filter-label">System Role <span class="reqStar">*</span></label>
                    <select id="role" name="role" x-model="selectedRole" required
                        class="form-select text-sm font-medium text-sky-900">
                        <option value="admin">Administrator (Full Global Access)</option>
                        <option value="branch_manager">Branch Manager (Scoped to Branch)</option>
                        <option value="store_manager">Store Manager (Scoped to Store Outlet)</option>
                    </select>
                </div>

                <!-- Branch Selector (if Branch Manager) -->
                <div x-show="selectedRole === 'branch_manager'" x-transition>
                    <label for="branch_id" class="filter-label">Assigned Branch <span class="reqStar">*</span></label>
                    <select id="branch_id" name="branch_id" required class="form-select text-sm">
                        <option value="">-- Select Branch --</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ old('branch_id', $user->branch_id) == $b->id ? 'selected' : '' }}>
                                {{ $b->name }} ({{ $b->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Store Selector (if Store Manager) -->
                <div x-show="selectedRole === 'store_manager'" x-transition>
                    <label for="store_id" class="filter-label">Assigned Store Outlet <span class="reqStar">*</span></label>
                    <select id="store_id" name="store_id" required class="form-select text-sm font-medium">
                        <option value="">-- Select Store --</option>
                        @foreach($stores as $st)
                            <option value="{{ $st->id }}" {{ old('store_id', $user->store_id) == $st->id ? 'selected' : '' }}>
                                {{ $st->name }} ({{ $st->branch->name }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="pt-2">
                    <label class="flex items-center gap-2 text-xs font-semibold text-slate-700 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }} class="rounded text-sky-600">
                        <span>Active Account</span>
                    </label>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                    <div>
                        @if($user->id !== auth()->id())
                            <button type="button"
                                onclick="if(confirm('Are you sure you want to delete this user?')) document.getElementById('delete-user-form').submit();"
                                class="btn btn-danger btn-sm flex items-center gap-1.5">
                                <i class="fa-solid fa-trash-can text-xs"></i>
                                <span>Delete User</span>
                            </button>
                        @endif
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary flex items-center gap-1.5">
                            <i class="fa-solid fa-floppy-disk text-xs"></i>
                            <span>Update User</span>
                        </button>
                    </div>
                </div>
            </form>

            @if($user->id !== auth()->id())
                <form id="delete-user-form" method="POST" action="{{ route('users.destroy', $user) }}" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
            @endif
        </div>
    </div>

    @push('scripts')
        <script>
            function userEditForm() {
                return {
                    selectedRole: "{{ old('role', $user->role->value ?? $user->role) }}"
                };
            }
        </script>
    @endpush
@endsection