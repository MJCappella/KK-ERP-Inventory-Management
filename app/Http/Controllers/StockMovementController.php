<?php

namespace App\Http\Controllers;

use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Store;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $accessibleStores = Store::accessibleBy($user)->with('branch')->get();

        $query = StockMovement::accessibleBy($user)
            ->with(['store.branch', 'product', 'user']);

        if ($request->filled('store_id')) {
            $query->where('store_id', $request->store_id);
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('remarks', 'like', "%{$search}%")
                    ->orWhereHas('product', function ($pq) use ($search) {
                        $pq->where('name', 'like', "%{$search}%")
                            ->orWhere('sku', 'like', "%{$search}%");
                    });
            });
        }

        $movements = $query->latest('created_at')->paginate(20)->withQueryString();
        $products = Product::orderBy('name')->get();
        $types = StockMovementType::cases();

        return view('movements.index', compact('movements', 'accessibleStores', 'products', 'types'));
    }
}
