<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\DatabaseSeeder;
use App\Models\User;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }

    public function test_login_page_renders_with_demo_credentials(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Quick Demo Logins');
        $response->assertSee('admin@paim.ai');
    }

    public function test_authenticated_admin_can_access_dashboard(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('email', 'admin@paim.ai')->first();

        $response = $this->actingAs($admin)->get('/');

        $response->assertStatus(200);
        $response->assertSee('Actual Spend');
        $response->assertSee('ChatGPT Plus');
    }

    public function test_authenticated_admin_can_access_settings(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('email', 'admin@paim.ai')->first();

        $response = $this->actingAs($admin)->get('/settings');

        $response->assertStatus(200);
        $response->assertSee('System Settings');
        $response->assertSee('Regional Preferences');
    }

    public function test_viewer_role_cannot_access_settings(): void
    {
        $this->seed(DatabaseSeeder::class);
        $viewer = User::where('email', 'viewer@paim.ai')->first();

        $response = $this->actingAs($viewer)->get('/settings');

        $response->assertStatus(403);
    }

    public function test_manager_role_cannot_access_settings(): void
    {
        $this->seed(DatabaseSeeder::class);
        $manager = User::where('email', 'manager@paim.ai')->first();

        $response = $this->actingAs($manager)->get('/settings');

        $response->assertStatus(403);
    }

    public function test_authenticated_user_can_access_profile_page(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('email', 'admin@paim.ai')->first();

        $response = $this->actingAs($admin)->get('/profile');

        $response->assertStatus(200);
        $response->assertSee('Personal Profile Data');
        $response->assertSee('Security');
    }

    public function test_user_can_update_profile_info(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('email', 'admin@paim.ai')->first();

        $response = $this->actingAs($admin)->post('/profile/update', [
            'name' => 'Admin Boss',
            'email' => 'admin@paim.ai',
        ]);

        $response->assertRedirect('/profile');
        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'name' => 'Admin Boss',
        ]);
    }

    public function test_authenticated_admin_can_access_permissions_matrix(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('email', 'admin@paim.ai')->first();

        $response = $this->actingAs($admin)->get('/permissions');

        $response->assertStatus(200);
        $response->assertSee('Granular Role Permission Matrix');
        $response->assertSee('subscriptions.view');
    }

    public function test_viewer_role_cannot_post_subscriptions(): void
    {
        $this->seed(DatabaseSeeder::class);
        $viewer = User::where('email', 'viewer@paim.ai')->first();

        $response = $this->actingAs($viewer)->post('/subscriptions', [
            'name' => 'Test Sub',
            'tool_name' => 'Test Tool',
            'type' => 'monthly_recurring',
            'recurring_amount' => 10,
            'billing_cadence_months' => 1,
            'start_date' => date('Y-m-d'),
        ]);

        $response->assertSessionHas('error');
    }
}
