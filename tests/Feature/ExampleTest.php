<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders_clean_login_form_without_demo_personas(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Sign in to your account');
        $response->assertSee('Email Address');
        $response->assertSee('Password');
        $response->assertDontSee('1-Click Test Personas');
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::create([
            'name' => 'Alice Admin',
            'email' => 'alice@kkwholesalers.com',
            'password' => bcrypt('secret123'),
            'role' => Role::ADMIN,
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'alice@kkwholesalers.com',
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        User::create([
            'name' => 'Bob Manager',
            'email' => 'bob@kkwholesalers.com',
            'password' => bcrypt('secret123'),
            'role' => Role::STORE_MANAGER,
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'bob@kkwholesalers.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_authenticated_user_can_logout_via_post(): void
    {
        $user = User::create([
            'name' => 'Alice Admin',
            'email' => 'alice@kkwholesalers.com',
            'password' => bcrypt('secret123'),
            'role' => Role::ADMIN,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('info', 'You have been logged out.');
        $this->assertGuest();
    }

    public function test_authenticated_user_can_logout_via_get(): void
    {
        $user = User::create([
            'name' => 'Alice Admin',
            'email' => 'alice@kkwholesalers.com',
            'password' => bcrypt('secret123'),
            'role' => Role::ADMIN,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get('/logout');

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('info', 'You have been logged out.');
        $this->assertGuest();
    }

    public function test_unauthenticated_user_accessing_logout_redirects_to_login(): void
    {
        $response = $this->get('/logout');

        $response->assertRedirect(route('login'));
    }
}
