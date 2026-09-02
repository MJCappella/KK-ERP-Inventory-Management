<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Branch;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacScopingTest extends TestCase
{
    use RefreshDatabase;

    protected Branch $branch1;
    protected Branch $branch2;
    protected Store $b1Store1;
    protected Store $b2Store1;
    protected Store $b2Store2;
    protected User $admin;
    protected User $bm1;
    protected User $bm2;
    protected User $sm1;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch1 = Branch::create(['name' => 'Branch 1', 'code' => 'BR-01']);
        $this->branch2 = Branch::create(['name' => 'Branch 2', 'code' => 'BR-02']);

        $this->b1Store1 = Store::create(['branch_id' => $this->branch1->id, 'name' => 'B1 Store 1', 'code' => 'STR-1-1']);
        $this->b2Store1 = Store::create(['branch_id' => $this->branch2->id, 'name' => 'B2 Store 1', 'code' => 'STR-2-1']);
        $this->b2Store2 = Store::create(['branch_id' => $this->branch2->id, 'name' => 'B2 Store 2', 'code' => 'STR-2-2']);

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password123'),
            'role' => Role::ADMIN,
        ]);

        $this->bm1 = User::create([
            'name' => 'Branch 1 Manager',
            'email' => 'bm1@test.com',
            'password' => bcrypt('password123'),
            'role' => Role::BRANCH_MANAGER,
            'branch_id' => $this->branch1->id,
        ]);

        $this->bm2 = User::create([
            'name' => 'Branch 2 Manager',
            'email' => 'bm2@test.com',
            'password' => bcrypt('password123'),
            'role' => Role::BRANCH_MANAGER,
            'branch_id' => $this->branch2->id,
        ]);

        $this->sm1 = User::create([
            'name' => 'Store 1 Manager',
            'email' => 'sm1@test.com',
            'password' => bcrypt('password123'),
            'role' => Role::STORE_MANAGER,
            'branch_id' => $this->branch1->id,
            'store_id' => $this->b1Store1->id,
        ]);
    }

    public function test_admin_has_access_to_all_stores_and_branches(): void
    {
        $this->assertTrue($this->admin->canAccessStore($this->b1Store1));
        $this->assertTrue($this->admin->canAccessStore($this->b2Store1));
        $this->assertTrue($this->admin->canAccessStore($this->b2Store2));
        $this->assertEquals(3, count($this->admin->accessibleStoreIds()));
    }

    public function test_branch_manager_is_scoped_to_assigned_branch_stores_only(): void
    {
        // BM 2 can access B2 stores, but NOT B1 store
        $this->assertTrue($this->bm2->canAccessStore($this->b2Store1));
        $this->assertTrue($this->bm2->canAccessStore($this->b2Store2));
        $this->assertFalse($this->bm2->canAccessStore($this->b1Store1));

        $b2Stores = Store::accessibleBy($this->bm2)->pluck('id')->toArray();
        $this->assertContains($this->b2Store1->id, $b2Stores);
        $this->assertContains($this->b2Store2->id, $b2Stores);
        $this->assertNotContains($this->b1Store1->id, $b2Stores);
    }

    public function test_store_manager_is_scoped_to_assigned_store_only(): void
    {
        $this->assertTrue($this->sm1->canAccessStore($this->b1Store1));
        $this->assertFalse($this->sm1->canAccessStore($this->b2Store1));
        $this->assertFalse($this->sm1->canAccessStore($this->b2Store2));

        $accessibleStores = Store::accessibleBy($this->sm1)->pluck('id')->toArray();
        $this->assertEquals([$this->b1Store1->id], $accessibleStores);
    }
}
