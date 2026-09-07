<?php

namespace App\Services;

use App\Enums\StockMovementType;
use App\Enums\TransferStatus;
use App\Models\Product;
use App\Models\Store;
use App\Models\Transfer;
use App\Models\TransferItem;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TransferService
{
    public function __construct(protected InventoryService $inventoryService) {}

    /**
     * Execute a direct store-to-store stock transfer atomically.
     *
     * @param  array<array{product_id: int, quantity: int}>  $items
     *
     * @throws Exception
     */
    public function executeDirectTransfer(
        int $sourceStoreId,
        int $destStoreId,
        array $items,
        int $userId,
        ?string $notes = null
    ): Transfer {
        Log::channel('audit')->info('[INTER-STORE TRANSFER INITIATED]', [
            'source_store_id' => $sourceStoreId,
            'dest_store_id' => $destStoreId,
            'items_count' => count($items),
            'user_id' => $userId,
        ]);

        if ($sourceStoreId === $destStoreId) {
            Log::channel('audit')->warning('[TRANSFER REJECTED] Source and Destination stores are identical', [
                'store_id' => $sourceStoreId,
                'user_id' => $userId,
            ]);
            throw new Exception('Source and Destination store cannot be the same.');
        }

        if (empty($items)) {
            Log::channel('audit')->warning('[TRANSFER REJECTED] Empty transfer items list', [
                'source_store_id' => $sourceStoreId,
                'dest_store_id' => $destStoreId,
                'user_id' => $userId,
            ]);
            throw new Exception('Transfer must contain at least one product item.');
        }

        $sourceStore = Store::findOrFail($sourceStoreId);
        $destStore = Store::findOrFail($destStoreId);

        try {
            return DB::transaction(function () use ($sourceStore, $destStore, $items, $userId, $notes) {
                // Create Transfer header
                $transferNumber = 'TRF-'.strtoupper(Str::random(8));

                $transfer = Transfer::create([
                    'source_store_id' => $sourceStore->id,
                    'destination_store_id' => $destStore->id,
                    'user_id' => $userId,
                    'transfer_number' => $transferNumber,
                    'status' => TransferStatus::COMPLETED,
                    'notes' => $notes,
                ]);

                foreach ($items as $index => $item) {
                    $productId = (int) ($item['product_id'] ?? 0);
                    $quantity = (int) ($item['quantity'] ?? 0);

                    if ($quantity <= 0) {
                        Log::channel('audit')->warning('[TRANSFER REJECTED] Non-positive quantity for product', [
                            'item_index' => $index,
                            'product_id' => $productId,
                            'quantity' => $quantity,
                            'transfer_id' => $transfer->id,
                        ]);
                        throw new Exception('Transfer quantity must be greater than zero.');
                    }

                    $product = Product::findOrFail($productId);

                    // Create Transfer Item
                    TransferItem::create([
                        'transfer_id' => $transfer->id,
                        'product_id' => $productId,
                        'quantity' => $quantity,
                    ]);

                    // 1. Deduct from Source Store (Transfer Out)
                    $this->inventoryService->recordStockMovement(
                        storeId: $sourceStore->id,
                        productId: $productId,
                        quantityChange: -$quantity,
                        type: StockMovementType::TRANSFER_OUT,
                        userId: $userId,
                        referenceType: Transfer::class,
                        referenceId: $transfer->id,
                        remarks: "Transferred {$quantity}x {$product->name} to {$destStore->name} ({$transferNumber})"
                    );

                    // 2. Add to Destination Store (Transfer In)
                    $this->inventoryService->recordStockMovement(
                        storeId: $destStore->id,
                        productId: $productId,
                        quantityChange: $quantity,
                        type: StockMovementType::TRANSFER_IN,
                        userId: $userId,
                        referenceType: Transfer::class,
                        referenceId: $transfer->id,
                        remarks: "Received {$quantity}x {$product->name} from {$sourceStore->name} ({$transferNumber})"
                    );
                }

                Log::channel('audit')->info('[INTER-STORE TRANSFER COMPLETED]', [
                    'transfer_id' => $transfer->id,
                    'transfer_number' => $transferNumber,
                    'source_store' => $sourceStore->name,
                    'dest_store' => $destStore->name,
                    'items_count' => count($items),
                    'user_id' => $userId,
                ]);

                return $transfer->load(['sourceStore.branch', 'destinationStore.branch', 'user', 'items.product']);
            });
        } catch (\Throwable $e) {
            Log::channel('audit')->error('[INTER-STORE TRANSFER FAILED]', [
                'source_store_id' => $sourceStoreId,
                'dest_store_id' => $destStoreId,
                'items' => $items,
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            throw $e;
        }
    }
}
