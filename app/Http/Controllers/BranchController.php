<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BranchController extends Controller
{
    // get all branches
    public function index(Request $request)
    {
        $user = $request->user();
        $branches = Branch::accessibleBy($user)
            ->with(['stores.stocks.product', 'users'])
            ->withCount('stores')
            ->get()
            ->map(function ($branch) {
                $totalUnits = 0;
                $totalValuation = 0.0;
                foreach ($branch->stores as $store) {
                    foreach ($store->stocks as $stock) {
                        $totalUnits += $stock->quantity;
                        $totalValuation += $stock->quantity * ($stock->product->selling_price ?? 0);
                    }
                }
                $branch->total_stock_units = $totalUnits;
                $branch->total_stock_valuation = $totalValuation;

                return $branch;
            });

        return view('branches.index', compact('branches', 'user'));
    }

    // create branches
    public function create()
    {
        if (! auth()->user()->isAdmin()) {
            Log::channel('security')->warning('[UNAUTHORIZED BRANCH CREATION ATTEMPT]', [
                'user_id' => auth()->id(),
                'role' => auth()->user()->role->value ?? (string) auth()->user()->role,
            ]);
            abort(403, 'Only administrators can create branches.');
        }

        return view('branches.create');
    }

    // store branches
    public function store(Request $request)
    {
        if (! auth()->user()->isAdmin()) {
            Log::channel('security')->warning('[UNAUTHORIZED BRANCH CREATION ATTEMPT]', [
                'user_id' => auth()->id(),
                'role' => auth()->user()->role->value ?? (string) auth()->user()->role,
            ]);
            abort(403, 'Only administrators can create branches.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:branches,code',
            'location' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        $branch = Branch::create($validated);

        Log::channel('operations')->info('[BRANCH CREATED]', [
            'branch_id' => $branch->id,
            'name' => $branch->name,
            'code' => $branch->code,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('branches.index')->with('success', "Branch '{$branch->name}' created successfully.");
    }

    // edit branches
    public function edit(Branch $branch)
    {
        if (! auth()->user()->isAdmin()) {
            Log::channel('security')->warning('[UNAUTHORIZED BRANCH EDIT ATTEMPT]', [
                'user_id' => auth()->id(),
                'branch_id' => $branch->id,
                'role' => auth()->user()->role->value ?? (string) auth()->user()->role,
            ]);
            abort(403, 'Only administrators can edit branches.');
        }

        return view('branches.edit', compact('branch'));
    }

    // update branches
    public function update(Request $request, Branch $branch)
    {
        if (! auth()->user()->isAdmin()) {
            Log::channel('security')->warning('[UNAUTHORIZED BRANCH UPDATE ATTEMPT]', [
                'user_id' => auth()->id(),
                'branch_id' => $branch->id,
                'role' => auth()->user()->role->value ?? (string) auth()->user()->role,
            ]);
            abort(403, 'Only administrators can edit branches.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:branches,code,'.$branch->id,
            'location' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        $branch->update($validated);

        Log::channel('operations')->info('[BRANCH UPDATED]', [
            'branch_id' => $branch->id,
            'name' => $branch->name,
            'code' => $branch->code,
            'updated_by' => auth()->id(),
            'changes' => array_keys($validated),
        ]);

        return redirect()->route('branches.index')->with('success', "Branch '{$branch->name}' updated successfully.");
    }
}
