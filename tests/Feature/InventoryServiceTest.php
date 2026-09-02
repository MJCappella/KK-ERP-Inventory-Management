<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Enums\StockMovementType;
use App\Models\Branch;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Store;
use App\Models\StoreStock;
use App\Models\User;
use App\Services\InventoryService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryServiceTest extends TestCase
{
    use RefreshDatabase;

    protected Branch $branch;
    protected Store $store;
    protected User $user;
    protected Product $product;
    protected InventoryService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create([
            'name' => 'Branch 1',
            'code' => 'BR-01',
        ]);

        $this->store = Store::create([
            'branch_id' => $this->branch->id,
            'name' => 'Main Store',
            'code' => 'STR-01',
        ]);

        $this->user = User::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password123'),
            'role' => Role::ADMIN,
        ]);

        $this->product = Product::create([
            'name' => 'Basmati Rice 25kg',
            'sku' => 'SKU-RICE-25KG',
            'category' => 'Grains',
            'cost_price' => 2000,
            'selling_price' => 2500,
            'reorder_level' => 10,
        ]);

        $this->service = app(InventoryService::class);
    }

    public function test_prevents_stock_deduction_when_quantity_is_insufficient(): void
    {
        StoreStock::create([
            'store_id' => $this->store->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Insufficient stock');

        $this->service->recordStockMovement(
            storeId: $this->store->id,
            productId: $this->product->id,
            quantityChange: -5,
            type: StockMovementType::SALE,
            userId: $this->user->id
        );
    }

    public function test_correctly_records_stock_inbound_and_updates_balance(): void
    {
        $movement = $this->service->receiveStock(
            storeId: $this->store->id,
            productId: $this->product->id,
            quantity: 50,
            userId: $this->user->id,
            remarks: 'Supplier shipment'
        );

        $this->assertEquals(50, $movement->balance_after);
        $this->assertEquals(StockMovementType::INBOUND, $movement->type);

        $stock = StoreStock::where('store_id', $this->store->id)
            ->where('product_id', $this->product->id)
            ->first();

        $this->assertEquals(50, $stock->quantity);
        $this->assertDatabaseHas('stock_movements', [
            'store_id' => $this->store->id,
            'product_id' => $this->product->id,
            'quantity' => 50,
            'balance_after' => 50,
        ]);
    }

    public function test_correctly_performs_manual_stock_adjustment(): void
    {
        StoreStock::create([
            'store_id' => $this->store->id,
            'product_id' => $this->product->id,
            'quantity' => 30,
        ]);

        $movement = $this->service->adjustStock(
            storeId: $this->store->id,
            productId: $this->product->id,
            newQuantity: 25,
            userId: $this->user->id,
            reason: 'Audit breakage write-off'
        );

        $this->assertEquals(-5, $movement->quantity);
        $this->assertEquals(25, $movement->balance_after);
        $this->assertEquals(StockMovementType::ADJUSTMENT, $movement->type);

        $stock = StoreStock::where('store_id', $this->store->id)
            ->where('product_id', $this->product->id)
            ->first();

        $this->assertEquals(25, $stock->quantity);
    }
}
