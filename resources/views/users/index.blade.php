@extends('layouts.app')

@section('title', 'User Accounts & Roles')

@section('content')
    <div class="space-y-6">

        <!-- Page Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="page-title">User Accounts & Role-Based Access (RBAC)</h1>
                <p class="page-subtitle">Configure system users, assign roles and restrict operational scopes.</p>
            </div>
            <div>
                <a href="{{ route('users.create') }}" class="btn btn-primary shadow-sm shadow-sky-600/30 flex items-center gap-1.5">
                    <i class="fa-solid fa-user-plus text-xs"></i>
                    <span>Add User Account</span>
                </a>
            </div>
        </div>

        <!-- Users Table Card -->
        <div class="card">
            <div class="card-header">
                <div>
                    <h2 class="card-title">System Users
                        <span class="badge bg-sky-600 text-white font-semibold ml-2">{{ $users->total() }}</span>
                    </h2>
                    <p class="text-xs text-slate-500">Security permissions and branch/store scopes.</p>
                </div>
            </div>

            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>User Name</th>
                            <th>Email / Login</th>
                            <th>Role</th>
                            <th>Assigned Scope (Branch / Store)</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-8 h-8 rounded-full bg-sky-600 text-white font-bold text-xs flex items-center justify-center">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-900">{{ $user->name }}</div>
                                            <div class="text-[11px] text-slate-500">{{ $user->phone ?: 'No phone' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-xs text-slate-700">
                                    {{ $user->email }}
                                </td>
                                <td>
                                    <span class="badge {{ $user->role->badgeClass() }}">
                                        {{ $user->role->label() }}
                                    </span>
                                </td>
                                <td>
                                    @if($user->isAdmin())
                                        <span class="text-xs font-bold text-blue-700">Global (All Branches & Stores)</span>
                                    @elseif($user->isBranchManager())
                                        <div class="text-xs font-semibold text-slate-900">{{ $user->branch?->name ?? 'Unassigned' }}
                                        </div>
                                        <span class="text-[10px] text-slate-400">All Stores in Branch</span>
                                    @elseif($user->isStoreManager())
                                        <div class="text-xs font-semibold text-slate-900">{{ $user->store?->name ?? 'Unassigned' }}
                                        </div>
                                        <span class="text-[10px] text-slate-400">{{ $user->store?->branch?->name }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->is_active)
                                        <span class="badge bg-emerald-500 text-white font-bold badge-sm">Active</span>
                                    @else
                                        <span class="badge bg-yellow-500 text-white font-bold badge-sm">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex items-center gap-1.5">
                                        <a href="{{ route('users.edit', $user) }}" class="btn btn-secondary btn-sm flex items-center gap-1.5">
                                            <i class="fa-solid fa-pen-to-square text-xs"></i>
                                            <span>Edit</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($users->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $users->links() }}
                </div>
            @endif
        </div>

    </div>
@endsection