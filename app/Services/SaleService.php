<?php

namespace App\Services;

use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Store;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SaleService
{
    public function __construct(protected InventoryService $inventoryService) {}

    /**
     * Atomically process a Point of Sale (POS) checkout transaction and deduct stock.
     *
     * @param  array<array{product_id: int, quantity: int, unit_price?: float}>  $items
     *
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
        Log::channel('audit')->info('[POS SALE INITIATED]', [
            'store_id' => $storeId,
            'user_id' => $userId,
            'item_count' => count($items),
            'payment_method' => $paymentMethod,
            'customer_name' => $customerName,
            'discount_amount' => $discountAmount,
            'tax_amount' => $taxAmount,
        ]);

        if (empty($items)) {
            Log::channel('audit')->warning('[POS SALE REJECTED] Cart is empty', [
                'store_id' => $storeId,
                'user_id' => $userId,
            ]);
            throw new Exception('Sale cart is empty. Add at least one product.');
        }

        $store = Store::findOrFail($storeId);

        try {
            return DB::transaction(function () use ($store, $userId, $items, $paymentMethod, $customerName, $customerPhone, $notes, $discountAmount, $taxAmount) {
                $invoiceNumber = 'INV-'.date('Ymd').'-'.strtoupper(Str::random(5));

                $subtotal = 0.0;
                $saleItemsData = [];

                // Pre-process items and calculate totals
                foreach ($items as $index => $item) {
                    $productId = (int) ($item['product_id'] ?? 0);
                    $quantity = (int) ($item['quantity'] ?? 0);

                    if ($quantity <= 0) {
                        Log::channel('audit')->warning('[POS SALE REJECTED] Non-positive quantity', [
                            'store_id' => $store->id,
                            'user_id' => $userId,
                            'item_index' => $index,
                            'product_id' => $productId,
                            'quantity' => $quantity,
                        ]);
                        throw new Exception('Item quantity must be greater than zero.');
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

                Log::channel('audit')->info('[POS SALE COMPLETED]', [
                    'sale_id' => $sale->id,
                    'invoice_number' => $sale->invoice_number,
                    'store_id' => $store->id,
                    'store_name' => $store->name,
                    'user_id' => $userId,
                    'total_amount' => $totalAmount,
                    'subtotal' => $subtotal,
                    'items_count' => count($saleItemsData),
                    'payment_method' => $paymentMethod,
                ]);

                return $sale->load(['store.branch', 'user', 'items.product']);
            });
        } catch (\Throwable $e) {
            Log::channel('audit')->error('[POS SALE FAILED]', [
                'store_id' => $storeId,
                'user_id' => $userId,
                'items_count' => count($items),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            throw $e;
        }
    }
}
