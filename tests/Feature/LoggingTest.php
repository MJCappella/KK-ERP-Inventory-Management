<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreStock;
use App\Models\User;
use App\Services\InventoryService;
use App\Services\SaleService;
use App\Services\TransferService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Log\LogManager;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Mockery;
use Psr\Log\LoggerInterface;
use Tests\TestCase;

class LoggingTest extends TestCase
{
    use RefreshDatabase;

    protected Branch $branch;

    protected Store $store1;

    protected Store $store2;

    protected User $admin;

    protected User $storeManager;

    protected Product $product;

    protected $logSpy;

    protected $channelSpy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create(['name' => 'Nairobi Branch', 'code' => 'BR-NRB']);
        $this->store1 = Store::create(['branch_id' => $this->branch->id, 'name' => 'Store 1', 'code' => 'STR-01']);
        $this->store2 = Store::create(['branch_id' => $this->branch->id, 'name' => 'Store 2', 'code' => 'STR-02']);

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@kkwholesalers.com',
            'password' => bcrypt('password123'),
            'role' => Role::ADMIN,
            'is_active' => true,
        ]);

        $this->storeManager = User::create([
            'name' => 'Store Manager',
            'email' => 'manager@kkwholesalers.com',
            'password' => bcrypt('password123'),
            'role' => Role::STORE_MANAGER,
            'store_id' => $this->store1->id,
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'name' => 'Maize Flour 2kg',
            'sku' => 'MZ-FLR-2KG',
            'category' => 'Grains',
            'unit' => 'Pack',
            'cost_price' => 120.00,
            'selling_price' => 160.00,
            'reorder_level' => 10,
            'is_active' => true,
        ]);

        StoreStock::create(['store_id' => $this->store1->id, 'product_id' => $this->product->id, 'quantity' => 100]);
        StoreStock::create(['store_id' => $this->store2->id, 'product_id' => $this->product->id, 'quantity' => 20]);

        // Setup unified Mockery spies for root logger and channels
        $this->channelSpy = Mockery::spy(LoggerInterface::class);
        $this->logSpy = Mockery::spy(LogManager::class);
        $this->logSpy->shouldReceive('channel')->andReturn($this->channelSpy);
        Log::swap($this->logSpy);
    }

    public function test_http_requests_receive_x_request_id_header(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $requestId = $response->headers->get('X-Request-Id');
        $this->assertNotEmpty($requestId);
        $this->assertTrue(Str::isUuid($requestId));
    }

    public function test_incoming_request_is_logged_by_middleware(): void
    {
        $this->get('/login');

        $this->logSpy->shouldHaveReceived('info')->withArgs(function ($message, $context) {
            return str_contains($message, 'HTTP GET login') &&
                   isset($context['request_id']) &&
                   $context['status'] === 200;
        });
    }

    public function test_sensitive_request_parameters_are_masked_in_logs(): void
    {
        $this->post('/login', [
            'email' => 'unknown@user.com',
            'password' => 'super-secret-password-123',
        ]);

        // A redirect 302 logs as info by the middleware with sanitized parameters
        $this->logSpy->shouldHaveReceived('info')->withArgs(function ($message, $context) {
            if (isset($context['params']['password'])) {
                $this->assertEquals('********', $context['params']['password']);

                return true;
            }

            return false;
        });
    }

    public function test_failed_login_logs_to_security_channel(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@kkwholesalers.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');

        $this->channelSpy->shouldHaveReceived('warning')->withArgs(function ($message, $context) {
            return str_contains($message, '[USER LOGIN FAILED]') &&
                   $context['attempted_email'] === 'admin@kkwholesalers.com';
        });
    }

    public function test_successful_login_logs_to_security_channel(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@kkwholesalers.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');

        $this->channelSpy->shouldHaveReceived('info')->withArgs(function ($message, $context) {
            return str_contains($message, '[USER LOGIN SUCCESS]') &&
                   $context['user_id'] === $this->admin->id &&
                   $context['email'] === 'admin@kkwholesalers.com';
        });
    }

    public function test_role_middleware_logs_authorization_failure(): void
    {
        // Store manager attempts to access admin-only users index
        $response = $this->actingAs($this->storeManager)->get('/users');

        $response->assertStatus(403);

        $this->channelSpy->shouldHaveReceived('warning')->withArgs(function ($message, $context) {
            return str_contains($message, '[AUTHORIZATION FAILED]') &&
                   $context['user_id'] === $this->storeManager->id &&
                   $context['user_role'] === Role::STORE_MANAGER->value;
        });
    }

    public function test_pos_sale_checkout_logs_audit_trail(): void
    {
        $saleService = app(SaleService::class);

        $sale = $saleService->recordSale(
            storeId: $this->store1->id,
            userId: $this->storeManager->id,
            items: [
                ['product_id' => $this->product->id, 'quantity' => 2, 'unit_price' => 160.00],
            ],
            paymentMethod: 'mpesa'
        );

        $this->assertNotNull($sale->id);

        $this->channelSpy->shouldHaveReceived('info')->withArgs(function ($message, $context) {
            return str_contains($message, '[POS SALE INITIATED]') &&
                   $context['store_id'] === $this->store1->id;
        });

        $this->channelSpy->shouldHaveReceived('info')->withArgs(function ($message, $context) {
            return str_contains($message, '[POS SALE COMPLETED]') &&
                   $context['total_amount'] == 320.00;
        });
    }

    public function test_inventory_service_logs_audit_trail(): void
    {
        $inventoryService = app(InventoryService::class);

        // Receive stock
        $inventoryService->receiveStock(
            storeId: $this->store1->id,
            productId: $this->product->id,
            quantity: 50,
            userId: $this->admin->id,
            remarks: 'Supplier delivery batch #99'
        );

        $this->channelSpy->shouldHaveReceived('info')->withArgs(function ($message, $context) {
            return str_contains($message, '[INBOUND STOCK INITIATED]') &&
                   $context['quantity'] === 50;
        });

        // Adjust stock
        $inventoryService->adjustStock(
            storeId: $this->store1->id,
            productId: $this->product->id,
            newQuantity: 140,
            userId: $this->admin->id,
            reason: 'Physical count adjustment'
        );

        $this->channelSpy->shouldHaveReceived('info')->withArgs(function ($message, $context) {
            return str_contains($message, '[STOCK ADJUSTMENT INITIATED]') &&
                   $context['new_quantity'] === 140;
        });
    }

    public function test_inter_store_transfer_logs_audit_trail(): void
    {
        $transferService = app(TransferService::class);

        $transfer = $transferService->executeDirectTransfer(
            sourceStoreId: $this->store1->id,
            destStoreId: $this->store2->id,
            items: [
                ['product_id' => $this->product->id, 'quantity' => 10],
            ],
            userId: $this->admin->id,
            notes: 'Restock Store 2'
        );

        $this->assertNotNull($transfer->id);

        $this->channelSpy->shouldHaveReceived('info')->withArgs(function ($message, $context) {
            return str_contains($message, '[INTER-STORE TRANSFER INITIATED]') &&
                   $context['source_store_id'] === $this->store1->id &&
                   $context['dest_store_id'] === $this->store2->id;
        });

        $this->channelSpy->shouldHaveReceived('info')->withArgs(function ($message, $context) {
            return str_contains($message, '[INTER-STORE TRANSFER COMPLETED]');
        });
    }
}
