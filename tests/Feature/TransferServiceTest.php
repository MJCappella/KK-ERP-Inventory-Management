<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Enums\StockMovementType;
use App\Enums\TransferStatus;
use App\Models\Branch;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Store;
use App\Models\StoreStock;
use App\Models\User;
use App\Services\TransferService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransferServiceTest extends TestCase
{
    use RefreshDatabase;

    protected Branch $branch;
    protected Store $sourceStore;
    protected Store $destStore;
    protected User $user;
    protected Product $product;
    protected TransferService $transferService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create([
            'name' => 'Branch 2',
            'code' => 'BR-02',
        ]);

        $this->sourceStore = Store::create([
            'branch_id' => $this->branch->id,
            'name' => 'Source Warehouse',
            'code' => 'STR-SRC',
        ]);

        $this->destStore = Store::create([
            'branch_id' => $this->branch->id,
            'name' => 'Destination Outlet',
            'code' => 'STR-DST',
        ]);

        $this->user = User::create([
            'name' => 'Branch Manager',
            'email' => 'bm@test.com',
            'password' => bcrypt('password123'),
            'role' => Role::BRANCH_MANAGER,
            'branch_id' => $this->branch->id,
        ]);

        $this->product = Product::create([
            'name' => 'Refined Sugar 50kg',
            'sku' => 'SKU-SUGAR-50KG',
            'category' => 'Sugar',
            'cost_price' => 4500,
            'selling_price' => 5500,
            'reorder_level' => 10,
        ]);

        $this->transferService = app(TransferService::class);
    }

    public function test_correctly_updates_both_store_balances_during_direct_transfer(): void
    {
        StoreStock::create([
            'store_id' => $this->sourceStore->id,
            'product_id' => $this->product->id,
            'quantity' => 20,
        ]);

        StoreStock::create([
            'store_id' => $this->destStore->id,
            'product_id' => $this->product->id,
            'quantity' => 5,
        ]);

        $transfer = $this->transferService->executeDirectTransfer(
            sourceStoreId: $this->sourceStore->id,
            destStoreId: $this->destStore->id,
            items: [
                ['product_id' => $this->product->id, 'quantity' => 10],
            ],
            userId: $this->user->id,
            notes: 'Test store transfer'
        );

        $this->assertEquals(TransferStatus::COMPLETED, $transfer->status);

        // Check source decreased to 10
        $sourceStock = StoreStock::where('store_id', $this->sourceStore->id)
            ->where('product_id', $this->product->id)
            ->first();
        $this->assertEquals(10, $sourceStock->quantity);

        // Check dest increased to 15
        $destStock = StoreStock::where('store_id', $this->destStore->id)
            ->where('product_id', $this->product->id)
            ->first();
        $this->assertEquals(15, $destStock->quantity);

        // Check 2 movement ledger records created
        $this->assertEquals(2, StockMovement::count());
        $this->assertDatabaseHas('stock_movements', [
            'store_id' => $this->sourceStore->id,
            'product_id' => $this->product->id,
            'type' => StockMovementType::TRANSFER_OUT,
            'quantity' => -10,
            'balance_after' => 10,
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'store_id' => $this->destStore->id,
            'product_id' => $this->product->id,
            'type' => StockMovementType::TRANSFER_IN,
            'quantity' => 10,
            'balance_after' => 15,
        ]);
    }

    public function test_prevents_transfer_between_same_store(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Source and Destination store cannot be the same');

        $this->transferService->executeDirectTransfer(
            sourceStoreId: $this->sourceStore->id,
            destStoreId: $this->sourceStore->id,
            items: [
                ['product_id' => $this->product->id, 'quantity' => 5],
            ],
            userId: $this->user->id
        );
    }
}
