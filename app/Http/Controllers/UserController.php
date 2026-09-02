<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\Branch;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

// user management controller - scoped to admin only
class UserController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only administrators can manage users.');
        }

        $users = User::with(['branch', 'store.branch'])
            ->orderBy('name')
            ->paginate(15);

        return view('users.index', compact('users'));
    }

    public function create()
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only administrators can create users.');
        }

        $roles = Role::cases();
        $branches = Branch::all();
        $stores = Store::with('branch')->get();

        return view('users.create', compact('roles', 'branches', 'stores'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only administrators can create users.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:50',
            'password' => ['required', Password::defaults()],
            'role' => 'required|string|in:admin,branch_manager,store_manager',
            'branch_id' => 'nullable|required_if:role,branch_manager|exists:branches,id',
            'store_id' => 'nullable|required_if:role,store_manager|exists:stores,id',
        ]);

        if ($validated['role'] === 'store_manager' && !empty($validated['store_id'])) {
            $store = Store::find($validated['store_id']);
            $validated['branch_id'] = $store?->branch_id;
        }

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        return redirect()->route('users.index')->with('success', "User '{$user->name}' created successfully.");
    }

    public function edit(User $user)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only administrators can edit users.');
        }

        $roles = Role::cases();
        $branches = Branch::all();
        $stores = Store::with('branch')->get();

        return view('users.edit', compact('user', 'roles', 'branches', 'stores'));
    }

    public function update(Request $request, User $user)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only administrators can edit users.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:50',
            'password' => ['nullable', Password::defaults()],
            'role' => 'required|string|in:admin,branch_manager,store_manager',
            'branch_id' => 'nullable|required_if:role,branch_manager|exists:branches,id',
            'store_id' => 'nullable|required_if:role,store_manager|exists:stores,id',
            'is_active' => 'boolean',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        if ($validated['role'] === 'store_manager' && !empty($validated['store_id'])) {
            $store = Store::find($validated['store_id']);
            $validated['branch_id'] = $store?->branch_id;
        } elseif ($validated['role'] === 'branch_manager') {
            $validated['store_id'] = null;
        } elseif ($validated['role'] === 'admin') {
            $validated['branch_id'] = null;
            $validated['store_id'] = null;
        }

        $user->update($validated);

        return redirect()->route('users.index')->with('success', "User '{$user->name}' updated successfully.");
    }

    public function destroy(User $user)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only administrators can delete users.');
        }

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}
