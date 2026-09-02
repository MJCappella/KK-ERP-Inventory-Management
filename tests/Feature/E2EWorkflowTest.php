<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Enums\StockMovementType;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Store;
use App\Models\StoreStock;
use App\Models\Transfer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class E2EWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected Branch $branch1;
    protected Branch $branch2;
    protected Store $store1;
    protected Store $store2;
    protected Store $store3;
    protected User $admin;
    protected User $bm1;
    protected User $sm1;
    protected User $sm2;
    protected Product $rice;
    protected Product $sugar;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch1 = Branch::create(['name' => 'Branch 1', 'code' => 'BR-01']);
        $this->branch2 = Branch::create(['name' => 'Branch 2', 'code' => 'BR-02']);

        $this->store1 = Store::create(['branch_id' => $this->branch1->id, 'name' => 'Wholesale Central', 'code' => 'STR-01']);
        $this->store2 = Store::create(['branch_id' => $this->branch2->id, 'name' => 'Retail Hub', 'code' => 'STR-02']);
        $this->store3 = Store::create(['branch_id' => $this->branch2->id, 'name' => 'Bulk Depot', 'code' => 'STR-03']);

        $this->admin = User::create([
            'name' => 'System Administrator',
            'email' => 'admin@kkwholesalers.com',
            'password' => bcrypt('password123'),
            'role' => Role::ADMIN,
            'is_active' => true,
        ]);

        $this->bm1 = User::create([
            'name' => 'Branch 1 Manager',
            'email' => 'bm1@kkwholesalers.com',
            'password' => bcrypt('password123'),
            'role' => Role::BRANCH_MANAGER,
            'branch_id' => $this->branch1->id,
            'is_active' => true,
        ]);

        $this->sm1 = User::create([
            'name' => 'Store 1 Manager',
            'email' => 'sm1@kkwholesalers.com',
            'password' => bcrypt('password123'),
            'role' => Role::STORE_MANAGER,
            'branch_id' => $this->branch1->id,
            'store_id' => $this->store1->id,
            'is_active' => true,
        ]);

        $this->sm2 = User::create([
            'name' => 'Store 2 Manager',
            'email' => 'sm2@kkwholesalers.com',
            'password' => bcrypt('password123'),
            'role' => Role::STORE_MANAGER,
            'branch_id' => $this->branch2->id,
            'store_id' => $this->store2->id,
            'is_active' => true,
        ]);

        $this->rice = Product::create([
            'name' => 'Basmati Rice 25kg',
            'sku' => 'SKU-RICE-25KG',
            'category' => 'Grains',
            'cost_price' => 2400,
            'selling_price' => 3100,
            'reorder_level' => 10,
        ]);

        $this->sugar = Product::create([
            'name' => 'Refined Sugar 50kg',
            'sku' => 'SKU-SUGAR-50KG',
            'category' => 'Sugar',
            'cost_price' => 4800,
            'selling_price' => 5900,
            'reorder_level' => 10,
        ]);

        StoreStock::create(['store_id' => $this->store1->id, 'product_id' => $this->rice->id, 'quantity' => 50]);
        StoreStock::create(['store_id' => $this->store1->id, 'product_id' => $this->sugar->id, 'quantity' => 30]);

        StoreStock::create(['store_id' => $this->store2->id, 'product_id' => $this->rice->id, 'quantity' => 10]);
        StoreStock::create(['store_id' => $this->store2->id, 'product_id' => $this->sugar->id, 'quantity' => 5]);
    }

    public function test_dashboard_renders_successfully_for_authenticated_users(): void
    {
        $response = $this->actingAs($this->admin)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Dashboard');
        $response->assertSee('Total Sales Revenue');
    }

    public function test_pos_sale_checkout_flow(): void
    {
        $response = $this->actingAs($this->sm1)->post('/sales', [
            'store_id' => $this->store1->id,
            'payment_method' => 'cash',
            'customer_name' => 'Acme Supermarket',
            'items' => [
                ['product_id' => $this->rice->id, 'quantity' => 5, 'unit_price' => 3100],
            ],
        ]);

        $sale = Sale::latest()->first();
        $this->assertNotNull($sale);
        $this->assertEquals(15500.00, (float)$sale->total_amount);
        $response->assertRedirect(route('sales.show', $sale));

        // Check stock reduced from 50 to 45
        $stock = StoreStock::where('store_id', $this->store1->id)->where('product_id', $this->rice->id)->first();
        $this->assertEquals(45, $stock->quantity);
    }

    public function test_inter_store_transfer_flow(): void
    {
        $response = $this->actingAs($this->admin)->post('/transfers', [
            'source_store_id' => $this->store1->id,
            'destination_store_id' => $this->store2->id,
            'notes' => 'Transferring stock for customer rush',
            'items' => [
                ['product_id' => $this->rice->id, 'quantity' => 15],
            ],
        ]);

        $transfer = Transfer::latest()->first();
        $this->assertNotNull($transfer);
        $response->assertRedirect(route('transfers.show', $transfer));

        // Source: 50 - 15 = 35
        $sourceStock = StoreStock::where('store_id', $this->store1->id)->where('product_id', $this->rice->id)->first();
        $this->assertEquals(35, $sourceStock->quantity);

        // Destination: 10 + 15 = 25
        $destStock = StoreStock::where('store_id', $this->store2->id)->where('product_id', $this->rice->id)->first();
        $this->assertEquals(25, $destStock->quantity);
    }

    public function test_inbound_stock_receipt_flow(): void
    {
        $response = $this->actingAs($this->sm1)->post('/inventory/receive', [
            'store_id' => $this->store1->id,
            'product_id' => $this->rice->id,
            'quantity' => 20,
            'remarks' => 'Supplier PO 1234',
        ]);

        $response->assertRedirect(route('inventory.index', ['store_id' => $this->store1->id]));

        // Stock was 50 + 20 = 70
        $stock = StoreStock::where('store_id', $this->store1->id)->where('product_id', $this->rice->id)->first();
        $this->assertEquals(70, $stock->quantity);
    }

    public function test_manual_stock_adjustment_flow(): void
    {
        $response = $this->actingAs($this->sm1)->post('/inventory/adjust', [
            'store_id' => $this->store1->id,
            'product_id' => $this->sugar->id,
            'new_quantity' => 28, // was 30, delta -2
            'reason' => '2 bags damaged packaging',
        ]);

        $response->assertRedirect(route('inventory.index', ['store_id' => $this->store1->id]));

        $stock = StoreStock::where('store_id', $this->store1->id)->where('product_id', $this->sugar->id)->first();
        $this->assertEquals(28, $stock->quantity);
    }

    public function test_audit_movements_page_accessible_and_scoped(): void
    {
        $response = $this->actingAs($this->admin)->get('/movements');
        $response->assertStatus(200);
        $response->assertSee('Stock Movements Audit Ledger');
    }

    public function test_store_manager_cannot_sell_from_unauthorized_store(): void
    {
        // Store 1 manager tries to sell from Store 2
        $response = $this->actingAs($this->sm1)->post('/sales', [
            'store_id' => $this->store2->id,
            'payment_method' => 'cash',
            'items' => [
                ['product_id' => $this->rice->id, 'quantity' => 1],
            ],
        ]);

        $response->assertStatus(403);
    }

    public function test_non_admin_cannot_access_users_management(): void
    {
        $response = $this->actingAs($this->sm1)->get('/users');
        $response->assertStatus(403);
    }

    public function test_demo_switcher_switches_authenticated_user(): void
    {
        $response = $this->actingAs($this->admin)->post(route('switch-user', $this->sm2));
        $this->assertAuthenticatedAs($this->sm2);
    }
}
