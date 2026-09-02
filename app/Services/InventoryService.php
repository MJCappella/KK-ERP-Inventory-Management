<?php

namespace App\Services;

use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\StoreStock;
use Exception;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Atomically records stock movement and updates store stocks.
     *
     * @throws Exception
     */
    public function recordStockMovement(
        int $storeId,
        int $productId,
        int $quantityChange,
        StockMovementType|string $type,
        int $userId,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $remarks = null
    ): StockMovement {
        if ($quantityChange === 0) {
            throw new Exception("Stock quantity change cannot be zero.");
        }

        $typeEnum = is_string($type) ? StockMovementType::from($type) : $type;

        return DB::transaction(function () use ($storeId, $productId, $quantityChange, $typeEnum, $userId, $referenceType, $referenceId, $remarks) {
            // Pessimistic locking to prevent race conditions across concurrent requests
            $stock = StoreStock::where('store_id', $storeId)
                ->where('product_id', $productId)
                ->lockForUpdate()
                ->first();

            if (!$stock) {
                if ($quantityChange < 0) {
                    $product = Product::find($productId);
                    $productName = $product ? $product->name : "ID {$productId}";
                    throw new Exception("Product '{$productName}' has 0 stock in this store. Cannot deduct {$quantityChange} units.");
                }

                $stock = StoreStock::create([
                    'store_id' => $storeId,
                    'product_id' => $productId,
                    'quantity' => 0,
                ]);
            }

            if ($quantityChange < 0 && $stock->quantity < abs($quantityChange)) {
                $product = Product::find($productId);
                $productName = $product ? $product->name : "ID {$productId}";
                throw new Exception("Insufficient stock for '{$productName}'. Available: {$stock->quantity}, Requested: " . abs($quantityChange));
            }

            $stock->quantity += $quantityChange;
            $stock->save();

            // Record immutable audit ledger entry
            return StockMovement::create([
                'store_id' => $storeId,
                'product_id' => $productId,
                'user_id' => $userId,
                'type' => $typeEnum,
                'quantity' => $quantityChange,
                'balance_after' => $stock->quantity,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'remarks' => $remarks,
            ]);
        });
    }

    // Receive inbound inventory from supplier or purchase order.
    public function receiveStock(
        int $storeId,
        int $productId,
        int $quantity,
        int $userId,
        ?string $remarks = null
    ): StockMovement {
        if ($quantity <= 0) {
            throw new Exception("Received quantity must be greater than zero.");
        }

        return $this->recordStockMovement(
            storeId: $storeId,
            productId: $productId,
            quantityChange: $quantity,
            type: StockMovementType::INBOUND,
            userId: $userId,
            remarks: $remarks ?: "Inbound stock delivery"
        );
    }

    // Manually adjust store stock
    public function adjustStock(
        int $storeId,
        int $productId,
        int $newQuantity,
        int $userId,
        ?string $reason = null
    ): StockMovement {
        if ($newQuantity < 0) {
            throw new Exception("Adjusted stock quantity cannot be negative.");
        }

        return DB::transaction(function () use ($storeId, $productId, $newQuantity, $userId, $reason) {
            $stock = StoreStock::where('store_id', $storeId)
                ->where('product_id', $productId)
                ->lockForUpdate()
                ->first();

            $currentQty = $stock ? $stock->quantity : 0;
            $delta = $newQuantity - $currentQty;

            if ($delta === 0) {
                throw new Exception("Current stock is already {$currentQty}. No adjustment needed.");
            }

            return $this->recordStockMovement(
                storeId: $storeId,
                productId: $productId,
                quantityChange: $delta,
                type: StockMovementType::ADJUSTMENT,
                userId: $userId,
                remarks: $reason ?: "Stock count adjusted from {$currentQty} to {$newQuantity}"
            );
        });
    }

    // Get available stock for a product in a store.
    public function getStoreStock(int $storeId, int $productId): int
    {
        return (int) (StoreStock::where('store_id', $storeId)
            ->where('product_id', $productId)
            ->value('quantity') ?? 0);
    }
}
