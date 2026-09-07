<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Store;
use App\Models\Transfer;
use App\Services\TransferService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TransferController extends Controller
{
    public function __construct(protected TransferService $transferService) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $accessibleStores = Store::accessibleBy($user)->with('branch')->get();

        $query = Transfer::accessibleBy($user)
            ->with(['sourceStore.branch', 'destinationStore.branch', 'user', 'items.product']);

        if ($request->filled('source_store_id')) {
            $query->where('source_store_id', $request->source_store_id);
        }

        if ($request->filled('destination_store_id')) {
            $query->where('destination_store_id', $request->destination_store_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $transfers = $query->latest()->paginate(15)->withQueryString();

        return view('transfers.index', compact('transfers', 'accessibleStores'));
    }

    public function create(Request $request)
    {
        $user = $request->user();

        // Source stores user has access to initiate transfers from
        $sourceStores = Store::accessibleBy($user)->with('branch')->get();
        // Destination can be any store in the system (or within branch for branch managers)
        $allStores = $user->isAdmin()
            ? Store::with('branch')->get()
            : ($user->isBranchManager() ? Store::where('branch_id', $user->branch_id)->with('branch')->get() : Store::with('branch')->get());

        $products = Product::where('is_active', true)->orderBy('name')->get();

        $selectedSourceId = $request->source_store_id ?: ($user->store_id ?: $sourceStores->first()?->id);

        return view('transfers.create', compact('sourceStores', 'allStores', 'products', 'selectedSourceId'));
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'source_store_id' => 'required|exists:stores,id',
            'destination_store_id' => 'required|exists:stores,id|different:source_store_id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500',
        ]);

        $sourceStore = Store::findOrFail($validated['source_store_id']);

        if (! $user->canAccessStore($sourceStore)) {
            Log::channel('security')->warning('[UNAUTHORIZED TRANSFER ATTEMPT]', [
                'user_id' => $user->id,
                'source_store_id' => $sourceStore->id,
                'source_store_name' => $sourceStore->name,
                'user_role' => $user->role->value ?? (string) $user->role,
            ]);
            abort(403, 'Unauthorized to initiate transfers from this source store.');
        }

        try {
            $transfer = $this->transferService->executeDirectTransfer(
                sourceStoreId: (int) $validated['source_store_id'],
                destStoreId: (int) $validated['destination_store_id'],
                items: $validated['items'],
                userId: $user->id,
                notes: $validated['notes'] ?? null
            );

            return redirect()->route('transfers.show', $transfer)
                ->with('success', "Transfer #{$transfer->transfer_number} completed successfully!");
        } catch (Exception $e) {
            Log::error('[TRANSFER CONTROLLER ERROR] '.$e->getMessage(), [
                'user_id' => $user->id,
                'source_store_id' => $validated['source_store_id'],
                'destination_store_id' => $validated['destination_store_id'],
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(Request $request, Transfer $transfer)
    {
        $user = $request->user();

        if (! $user->isAdmin()) {
            $canAccessSource = $user->canAccessStore($transfer->source_store_id);
            $canAccessDest = $user->canAccessStore($transfer->destination_store_id);
            if (! $canAccessSource && ! $canAccessDest) {
                abort(403, 'Unauthorized to view this transfer.');
            }
        }

        $transfer->load([
            'sourceStore.branch',
            'destinationStore.branch',
            'user',
            'items.product',
            'stockMovements',
        ]);

        return view('transfers.show', compact('transfer'));
    }
}
