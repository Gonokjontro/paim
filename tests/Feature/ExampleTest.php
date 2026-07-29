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

    public function test_super_admin_can_access_saas_dashboard(): void
    {
        $this->seed(DatabaseSeeder::class);
        $superAdmin = User::where('email', 'superadmin@paim.ai')->first();

        $response = $this->actingAs($superAdmin)->get('/super-admin');

        $response->assertStatus(200);
        $response->assertSee('SaaS Platform Operator Control Center');
        $response->assertSee('Acme Corporation');
    }

    public function test_super_admin_can_access_organizations_management(): void
    {
        $this->seed(DatabaseSeeder::class);
        $superAdmin = User::where('email', 'superadmin@paim.ai')->first();

        $response = $this->actingAs($superAdmin)->get('/super-admin/organizations');

        $response->assertStatus(200);
        $response->assertSee('Customer Organization Tenants');
        $response->assertSee('acme-corp');
    }

    public function test_non_super_admin_cannot_access_super_admin_portal(): void
    {
        $this->seed(DatabaseSeeder::class);
        $orgAdmin = User::where('email', 'admin@paim.ai')->first();

        $response = $this->actingAs($orgAdmin)->get('/super-admin');

        $response->assertStatus(403);
    }

    public function test_authenticated_user_can_access_org_reports(): void
    {
        $this->seed(DatabaseSeeder::class);
        $orgAdmin = User::where('email', 'admin@paim.ai')->first();

        $response = $this->actingAs($orgAdmin)->get('/reports');

        $response->assertStatus(200);
        $response->assertSee('Organization Financial');
        $response->assertSee('Spend by AI Vendor');
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

    public function test_viewer_role_cannot_access_settings(): void
    {
        $this->seed(DatabaseSeeder::class);
        $viewer = User::where('email', 'viewer@paim.ai')->first();

        $response = $this->actingAs($viewer)->get('/settings');

        $response->assertStatus(403);
    }
}
