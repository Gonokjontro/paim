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

    public function test_authenticated_user_can_access_calendar(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('email', 'admin@paim.ai')->first();

        $response = $this->actingAs($admin)->get('/calendar');

        $response->assertStatus(200);
        $response->assertSee('Renewal Timeline');
        $response->assertSee('ChatGPT Plus Monthly');
    }

    public function test_user_can_download_ical_feed(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('email', 'admin@paim.ai')->first();

        $response = $this->actingAs($admin)->get('/calendar/export.ics');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/calendar; charset=utf-8');
        $this->assertStringContainsString('BEGIN:VCALENDAR', $response->getContent());
    }

    public function test_authenticated_user_can_access_projects(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('email', 'admin@paim.ai')->first();

        $response = $this->actingAs($admin)->get('/projects');

        $response->assertStatus(200);
        $response->assertSee('Projects');
        $response->assertSee('Tax Write-offs');
    }

    public function test_user_can_export_tax_report_csv(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('email', 'admin@paim.ai')->first();

        $response = $this->actingAs($admin)->get('/projects/export-tax-report');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_authenticated_admin_can_access_webhooks(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('email', 'admin@paim.ai')->first();

        $response = $this->actingAs($admin)->get('/webhooks');

        $response->assertStatus(200);
        $response->assertSee('Webhook Alerting Channels');
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

    public function test_authenticated_user_can_access_profile_page(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('email', 'admin@paim.ai')->first();

        $response = $this->actingAs($admin)->get('/profile');

        $response->assertStatus(200);
        $response->assertSee('Personal Profile Data');
        $response->assertSee('Security');
    }
}
