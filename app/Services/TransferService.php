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
use Illuminate\Support\Str;

class TransferService
{
    public function __construct(protected InventoryService $inventoryService) {}

    /**
     * Execute a direct store-to-store stock transfer atomically.
     *
     * @param int $sourceStoreId
     * @param int $destStoreId
     * @param array<array{product_id: int, quantity: int}> $items
     * @param int $userId
     * @param string|null $notes
     * @return Transfer
     * @throws Exception
     */
    public function executeDirectTransfer(
        int $sourceStoreId,
        int $destStoreId,
        array $items,
        int $userId,
        ?string $notes = null
    ): Transfer {
        if ($sourceStoreId === $destStoreId) {
            throw new Exception("Source and Destination store cannot be the same.");
        }

        if (empty($items)) {
            throw new Exception("Transfer must contain at least one product item.");
        }

        $sourceStore = Store::findOrFail($sourceStoreId);
        $destStore = Store::findOrFail($destStoreId);

        return DB::transaction(function () use ($sourceStore, $destStore, $items, $userId, $notes) {
            // Create Transfer header
            $transferNumber = 'TRF-' . strtoupper(Str::random(8));

            $transfer = Transfer::create([
                'source_store_id' => $sourceStore->id,
                'destination_store_id' => $destStore->id,
                'user_id' => $userId,
                'transfer_number' => $transferNumber,
                'status' => TransferStatus::COMPLETED,
                'notes' => $notes,
            ]);

            foreach ($items as $item) {
                $productId = (int) $item['product_id'];
                $quantity = (int) $item['quantity'];

                if ($quantity <= 0) {
                    throw new Exception("Transfer quantity must be greater than zero.");
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

            return $transfer->load(['sourceStore.branch', 'destinationStore.branch', 'user', 'items.product']);
        });
    }
}
