<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Services\InventoryService;
use App\Services\SaleService;
use App\Services\TransferService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with full scenario data.
     */
    public function run(): void
    {
        $inventoryService = app(InventoryService::class);
        $transferService = app(TransferService::class);
        $saleService = app(SaleService::class);

        // 1. Create Branches
        $branch1 = Branch::create([
            'name' => 'Branch 1 - Nairobi',
            'code' => 'BR-NRB',
            'location' => 'Nairobi CBD',
            'phone' => '+254 700 111 001',
        ]);

        $branch2 = Branch::create([
            'name' => 'Branch 2 - Kisumu',
            'code' => 'BR-KSM',
            'location' => 'Kisumu',
            'phone' => '+254 700 222 002',
        ]);

        // 2. Create Stores
        // Branch 1 with 1 store
        $store1 = Store::create([
            'branch_id' => $branch1->id,
            'name' => 'Superior Center',
            'code' => 'STR-NRB-01',
            'location' => 'Basement, Superior Center, Kimathi Street',
            'phone' => '+254 711 000 001',
        ]);

        // Branch 2 has 2 stores
        $store2 = Store::create([
            'branch_id' => $branch2->id,
            'name' => 'Mega City',
            'code' => 'STR-KSM-01',
            'location' => 'Shop 4, Mega City Mall, Kisumu',
            'phone' => '+254 722 000 002',
        ]);

        $store3 = Store::create([
            'branch_id' => $branch2->id,
            'name' => 'Lake side Distributors',
            'code' => 'STR-KSM-02',
            'location' => '1st Floor, Airtel Plaza, Kisumu',
            'phone' => '+254 733 000 003',
        ]);

        // 3. Create Users
        $defaultPassword = Hash::make('password123');

        $admin = User::create([
            'name' => 'System Administrator',
            'email' => 'admin@kkwholesalers.com',
            'password' => $defaultPassword,
            'role' => Role::ADMIN,
            'phone' => '+254 700 000 000',
            'is_active' => true,
        ]);

        $bm1 = User::create([
            'name' => 'James Mwangi (Branch 1 Mgr)',
            'email' => 'branch1manager@kk.com',
            'password' => $defaultPassword,
            'role' => Role::BRANCH_MANAGER,
            'branch_id' => $branch1->id,
            'phone' => '+254 711 111 222',
            'is_active' => true,
        ]);

        $bm2 = User::create([
            'name' => 'Susan Karanja (Branch 2 Mgr)',
            'email' => 'branch2manager@kk.com',
            'password' => $defaultPassword,
            'role' => Role::BRANCH_MANAGER,
            'branch_id' => $branch2->id,
            'phone' => '+254 722 222 333',
            'is_active' => true,
        ]);

        $sm1 = User::create([
            'name' => 'Peter Ochieng (Store 1 Mgr)',
            'email' => 'store1manager@kk.com',
            'password' => $defaultPassword,
            'role' => Role::STORE_MANAGER,
            'branch_id' => $branch1->id,
            'store_id' => $store1->id,
            'phone' => '+254 733 333 444',
            'is_active' => true,
        ]);

        $sm2 = User::create([
            'name' => 'Amina Salim (Store 2 Mgr)',
            'email' => 'store2manager@kk.com',
            'password' => $defaultPassword,
            'role' => Role::STORE_MANAGER,
            'branch_id' => $branch2->id,
            'store_id' => $store2->id,
            'phone' => '+254 744 444 555',
            'is_active' => true,
        ]);

        $sm3 = User::create([
            'name' => 'David Kiprono (Store 3 Mgr)',
            'email' => 'store3manager@kk.com',
            'password' => $defaultPassword,
            'role' => Role::STORE_MANAGER,
            'branch_id' => $branch2->id,
            'store_id' => $store3->id,
            'phone' => '+254 755 555 666',
            'is_active' => true,
        ]);

        // 4. Create Products
        $productsData = [
            [
                'name' => 'Basmati Rice 25kg (Super Grade)',
                'sku' => 'SKU-RICE-25KG',
                'barcode' => '616110001001',
                'category' => 'Grains & Cereals',
                'unit' => 'bag',
                'cost_price' => 2450.00,
                'selling_price' => 3150.00,
                'reorder_level' => 15,
                'description' => 'Premium long-grain aged aromatic Basmati rice in woven sack.',
            ],
            [
                'name' => 'Refined White Sugar 50kg',
                'sku' => 'SKU-SUGAR-50KG',
                'barcode' => '616110001002',
                'category' => 'Sugar & Sweeteners',
                'unit' => 'bag',
                'cost_price' => 4800.00,
                'selling_price' => 5850.00,
                'reorder_level' => 10,
                'description' => 'Double refined fortified white crystal sugar in heavy-duty packaging.',
            ],
            [
                'name' => 'Pure Vegetable Cooking Oil 20L Jerrycan',
                'sku' => 'SKU-OIL-20L',
                'barcode' => '616110001003',
                'category' => 'Edible Oils',
                'unit' => 'jerrycan',
                'cost_price' => 3200.00,
                'selling_price' => 3950.00,
                'reorder_level' => 12,
                'description' => 'Triple-refined cholesterol-free palm cooking oil with Vitamin A.',
            ],
            [
                'name' => 'Premium Bakers Wheat Flour 10kg',
                'sku' => 'SKU-FLOUR-10KG',
                'barcode' => '616110001004',
                'category' => 'Grains & Cereals',
                'unit' => 'bale',
                'cost_price' => 1100.00,
                'selling_price' => 1420.00,
                'reorder_level' => 20,
                'description' => 'High protein all-purpose self-rising wheat flour for bakeries.',
            ],
            [
                'name' => 'Whole Long Life Milk 12 x 1L Carton',
                'sku' => 'SKU-MILK-12PK',
                'barcode' => '616110001005',
                'category' => 'Dairy & Beverages',
                'unit' => 'carton',
                'cost_price' => 1050.00,
                'selling_price' => 1350.00,
                'reorder_level' => 25,
                'description' => 'UHT homogenized whole cow milk in tamper-evident Tetra Pak.',
            ],
        ];

        $createdProducts = [];
        foreach ($productsData as $data) {
            $createdProducts[] = Product::create($data);
        }

        // 5. Seed Initial Stock via Inbound Receipts (creating ledger entries)
        $stores = [$store1, $store2, $store3];
        $initialQuantities = [
            0 => [$store1->id => 120, $store2->id => 40, $store3->id => 180],
            1 => [$store1->id => 80, $store2->id => 25, $store3->id => 140],
            2 => [$store1->id => 90, $store2->id => 35, $store3->id => 110],
            3 => [$store1->id => 150, $store2->id => 60, $store3->id => 200],
            4 => [$store1->id => 110, $store2->id => 50, $store3->id => 160],
        ];

        foreach ($createdProducts as $index => $product) {
            foreach ($stores as $store) {
                $qty = $initialQuantities[$index][$store->id] ?? 50;
                $inventoryService->receiveStock(
                    storeId: $store->id,
                    productId: $product->id,
                    quantity: $qty,
                    userId: $admin->id,
                    remarks: "Initial warehouse inventory intake for {$store->name}"
                );
            }
        }

        // 6. Seed Sample Inter-Store Transfers
        $transferService->executeDirectTransfer(
            sourceStoreId: $store3->id,
            destStoreId: $store2->id,
            items: [
                ['product_id' => $createdProducts[0]->id, 'quantity' => 15],
                ['product_id' => $createdProducts[1]->id, 'quantity' => 10],
                ['product_id' => $createdProducts[2]->id, 'quantity' => 12],
            ],
            userId: $bm2->id,
            notes: 'Weekly store replenishment transfer from Bulk Depot to Retail Hub.'
        );

        // Transfer 2: Wholesale Central (Store 1) -> Store 3
        $transferService->executeDirectTransfer(
            sourceStoreId: $store1->id,
            destStoreId: $store3->id,
            items: [
                ['product_id' => $createdProducts[3]->id, 'quantity' => 20],
                ['product_id' => $createdProducts[4]->id, 'quantity' => 15],
            ],
            userId: $admin->id,
            notes: 'Inter-branch bulk re-allocation.'
        );

        // 7. Seed Sample POS Sales
        // Sale 1 at Store 1
        $saleService->recordSale(
            storeId: $store1->id,
            userId: $sm1->id,
            items: [
                ['product_id' => $createdProducts[0]->id, 'quantity' => 5],
                ['product_id' => $createdProducts[1]->id, 'quantity' => 3],
                ['product_id' => $createdProducts[2]->id, 'quantity' => 2],
            ],
            paymentMethod: 'mpesa',
            customerName: 'QuickMart Supermarkets Ltd',
            customerPhone: '+254 712 345 678',
            notes: 'Wholesale order with prompt delivery.'
        );

        // Sale 2 at Store 2
        $saleService->recordSale(
            storeId: $store2->id,
            userId: $sm2->id,
            items: [
                ['product_id' => $createdProducts[4]->id, 'quantity' => 4],
                ['product_id' => $createdProducts[5]->id, 'quantity' => 6],
                ['product_id' => $createdProducts[7]->id, 'quantity' => 5],
            ],
            paymentMethod: 'cash',
            customerName: 'Amani Grocery & Provisions',
            customerPhone: '+254 723 456 789',
            notes: 'Walk-in retail buyer.'
        );

        // Sale 3 at Store 3
        $saleService->recordSale(
            storeId: $store3->id,
            userId: $sm3->id,
            items: [
                ['product_id' => $createdProducts[0]->id, 'quantity' => 10],
                ['product_id' => $createdProducts[3]->id, 'quantity' => 15],
            ],
            paymentMethod: 'bank_transfer',
            customerName: 'Coast Bakers Association',
            customerPhone: '+254 734 567 890',
            notes: 'Commercial bakery supplies bulk purchase.'
        );
    }
}
