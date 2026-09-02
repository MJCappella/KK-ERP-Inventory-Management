<?php

namespace App\Services;

use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Store;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SaleService
{
    public function __construct(protected InventoryService $inventoryService)
    {
    }

    /**
     * Atomically process a Point of Sale (POS) checkout transaction and deduct stock.
     *
     * @param int $storeId
     * @param int $userId
     * @param array<array{product_id: int, quantity: int, unit_price?: float}> $items
     * @param string $paymentMethod
     * @param string|null $customerName
     * @param string|null $customerPhone
     * @param string|null $notes
     * @param float $discountAmount
     * @param float $taxAmount
     * @return Sale
     * @throws Exception
     */
    public function recordSale(
        int $storeId,
        int $userId,
        array $items,
        string $paymentMethod = 'cash',
        ?string $customerName = null,
        ?string $customerPhone = null,
        ?string $notes = null,
        float $discountAmount = 0.00,
        float $taxAmount = 0.00
    ): Sale {
        if (empty($items)) {
            throw new Exception("Sale cart is empty. Add at least one product.");
        }

        $store = Store::findOrFail($storeId);

        return DB::transaction(function () use ($store, $userId, $items, $paymentMethod, $customerName, $customerPhone, $notes, $discountAmount, $taxAmount) {
            $invoiceNumber = 'INV-' . date('Ymd') . '-' . strtoupper(Str::random(5));

            $subtotal = 0.0;
            $saleItemsData = [];

            // Pre-process items and calculate totals
            foreach ($items as $item) {
                $productId = (int) $item['product_id'];
                $quantity = (int) $item['quantity'];

                if ($quantity <= 0) {
                    throw new Exception("Item quantity must be greater than zero.");
                }

                $product = Product::findOrFail($productId);
                $unitPrice = isset($item['unit_price']) && is_numeric($item['unit_price']) && (float) $item['unit_price'] > 0
                    ? (float) $item['unit_price']
                    : (float) $product->selling_price;

                $totalPrice = $unitPrice * $quantity;
                $subtotal += $totalPrice;

                $saleItemsData[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'unit_cost' => (float) $product->cost_price,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                ];
            }

            $totalAmount = max(0, $subtotal + $taxAmount - $discountAmount);

            // Create Sale header
            $sale = Sale::create([
                'store_id' => $store->id,
                'user_id' => $userId,
                'invoice_number' => $invoiceNumber,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'payment_method' => $paymentMethod,
                'customer_name' => $customerName,
                'customer_phone' => $customerPhone,
                'notes' => $notes,
            ]);

            // Deduct stock and create sale items
            foreach ($saleItemsData as $itemData) {
                $product = $itemData['product'];
                $quantity = $itemData['quantity'];

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_cost' => $itemData['unit_cost'],
                    'unit_price' => $itemData['unit_price'],
                    'total_price' => $itemData['total_price'],
                ]);

                // Atomically deduct inventory
                $this->inventoryService->recordStockMovement(
                    storeId: $store->id,
                    productId: $product->id,
                    quantityChange: -$quantity,
                    type: StockMovementType::SALE,
                    userId: $userId,
                    referenceType: Sale::class,
                    referenceId: $sale->id,
                    remarks: "Sold {$quantity}x {$product->name} (Invoice #{$invoiceNumber})"
                );
            }

            return $sale->load(['store.branch', 'user', 'items.product']);
        });
    }
}
