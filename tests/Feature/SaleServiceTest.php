<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Enums\StockMovementType;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Store;
use App\Models\StoreStock;
use App\Models\User;
use App\Services\SaleService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleServiceTest extends TestCase
{
    use RefreshDatabase;

    protected Branch $branch;

    protected Store $store;

    protected User $user;

    protected Product $product1;

    protected Product $product2;

    protected SaleService $saleService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create([
            'name' => 'Branch 1',
            'code' => 'BR-01',
        ]);

        $this->store = Store::create([
            'branch_id' => $this->branch->id,
            'name' => 'Wholesale Outlet',
            'code' => 'STR-01',
        ]);

        $this->user = User::create([
            'name' => 'Store Manager',
            'email' => 'sm@test.com',
            'password' => bcrypt('password123'),
            'role' => Role::STORE_MANAGER,
            'branch_id' => $this->branch->id,
            'store_id' => $this->store->id,
        ]);

        $this->product1 = Product::create([
            'name' => 'Cooking Oil 20L',
            'sku' => 'SKU-OIL-20L',
            'category' => 'Oils',
            'cost_price' => 3000,
            'selling_price' => 3800,
            'reorder_level' => 5,
        ]);

        $this->product2 = Product::create([
            'name' => 'Wheat Flour 10kg',
            'sku' => 'SKU-FLOUR-10KG',
            'category' => 'Grains',
            'cost_price' => 1000,
            'selling_price' => 1400,
            'reorder_level' => 10,
        ]);

        $this->saleService = app(SaleService::class);
    }

    public function test_correctly_processes_sale_and_deducts_inventory(): void
    {
        StoreStock::create([
            'store_id' => $this->store->id,
            'product_id' => $this->product1->id,
            'quantity' => 15,
        ]);

        $sale = $this->saleService->recordSale(
            storeId: $this->store->id,
            userId: $this->user->id,
            items: [
                ['product_id' => $this->product1->id, 'quantity' => 3, 'unit_price' => 3800],
            ],
            paymentMethod: 'mpesa',
            customerName: 'Amani Stores',
            customerPhone: '+254711222333'
        );

        $this->assertInstanceOf(Sale::class, $sale);
        $this->assertStringStartsWith('INV-', $sale->invoice_number);
        $this->assertEquals(11400.00, (float) $sale->total_amount);

        // Check stock reduced from 15 to 12
        $stock = StoreStock::where('store_id', $this->store->id)
            ->where('product_id', $this->product1->id)
            ->first();
        $this->assertEquals(12, $stock->quantity);

        // Check Sale item recorded
        $this->assertDatabaseHas('sale_items', [
            'sale_id' => $sale->id,
            'product_id' => $this->product1->id,
            'quantity' => 3,
            'unit_price' => 3800,
            'total_price' => 11400,
        ]);

        // Check stock movement logged
        $this->assertDatabaseHas('stock_movements', [
            'store_id' => $this->store->id,
            'product_id' => $this->product1->id,
            'type' => StockMovementType::SALE,
            'quantity' => -3,
            'balance_after' => 12,
            'reference_type' => Sale::class,
            'reference_id' => $sale->id,
        ]);
    }

    public function test_correctly_calculates_sale_with_discounts_and_taxes(): void
    {
        StoreStock::create([
            'store_id' => $this->store->id,
            'product_id' => $this->product1->id,
            'quantity' => 10,
        ]);

        StoreStock::create([
            'store_id' => $this->store->id,
            'product_id' => $this->product2->id,
            'quantity' => 10,
        ]);

        $sale = $this->saleService->recordSale(
            storeId: $this->store->id,
            userId: $this->user->id,
            items: [
                ['product_id' => $this->product1->id, 'quantity' => 2], // 2 * 3800 = 7600
                ['product_id' => $this->product2->id, 'quantity' => 1], // 1 * 1400 = 1400
            ],
            discountAmount: 500,
            taxAmount: 200
        );

        $this->assertEquals(9000.00, (float) $sale->subtotal);
        $this->assertEquals(500.00, (float) $sale->discount_amount);
        $this->assertEquals(200.00, (float) $sale->tax_amount);
        $this->assertEquals(8700.00, (float) $sale->total_amount); // 9000 + 200 - 500 = 8700
    }

    public function test_prevents_sale_when_requested_quantity_exceeds_store_stock(): void
    {
        StoreStock::create([
            'store_id' => $this->store->id,
            'product_id' => $this->product1->id,
            'quantity' => 2,
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Insufficient stock');

        $this->saleService->recordSale(
            storeId: $this->store->id,
            userId: $this->user->id,
            items: [
                ['product_id' => $this->product1->id, 'quantity' => 10],
            ]
        );
    }

    public function test_prevents_sale_with_empty_cart(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Sale cart is empty');

        $this->saleService->recordSale(
            storeId: $this->store->id,
            userId: $this->user->id,
            items: []
        );
    }
}
