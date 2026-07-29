<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organization;
use App\Models\Workspace;
use App\Models\User;
use App\Models\Category;
use App\Models\Vendor;
use App\Models\Tool;
use App\Models\PaymentAccount;
use App\Models\Subscription;
use App\Models\PlanVersion;
use App\Models\CostEntry;
use App\Models\MeterUnit;
use App\Models\TokenPackage;
use App\Models\UsageEntry;
use App\Models\Target;
use App\Models\Alert;
use App\Models\Project;
use App\Models\ProjectAllocation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Customer Organizations (Tenants)
        $orgAcme = Organization::create([
            'name' => 'Acme Corporation',
            'slug' => 'acme-corp',
            'plan_tier' => 'pro',
            'status' => 'active',
            'max_users' => 15,
            'max_subscriptions' => 50,
        ]);

        $orgCyberdyne = Organization::create([
            'name' => 'CyberDyne Systems',
            'slug' => 'cyberdyne',
            'plan_tier' => 'enterprise',
            'status' => 'active',
            'max_users' => 50,
            'max_subscriptions' => 200,
        ]);

        // 2. Global Super Admin Platform Operator User
        $superAdminUser = User::create([
            'organization_id' => null,
            'name' => 'Super Admin Operator',
            'email' => 'superadmin@paim.ai',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'status' => 'active',
            'avatar_url' => 'assets/media/avatars/300-1.jpg',
        ]);

        // 3. Workspace for Acme Corp
        $workspace = Workspace::create([
            'organization_id' => $orgAcme->id,
            'name' => 'Acme AI Production Workspace',
            'slug' => 'acme-ai',
            'time_zone' => 'UTC',
            'base_currency' => 'USD',
            'fiscal_year_start_month' => 1,
        ]);

        // 4. Acme Demo Users for Each Role
        $adminUser = User::create([
            'organization_id' => $orgAcme->id,
            'workspace_id' => $workspace->id,
            'name' => 'Admin User (Acme)',
            'email' => 'admin@paim.ai',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'active',
            'avatar_url' => 'assets/media/avatars/300-1.jpg',
        ]);

        $managerUser = User::create([
            'organization_id' => $orgAcme->id,
            'workspace_id' => $workspace->id,
            'name' => 'Manager User (Acme)',
            'email' => 'manager@paim.ai',
            'password' => Hash::make('password'),
            'role' => 'manager',
            'status' => 'active',
            'avatar_url' => 'assets/media/avatars/300-2.jpg',
        ]);

        $viewerUser = User::create([
            'organization_id' => $orgAcme->id,
            'workspace_id' => $workspace->id,
            'name' => 'Viewer User (Read Only)',
            'email' => 'viewer@paim.ai',
            'password' => Hash::make('password'),
            'role' => 'viewer',
            'status' => 'active',
            'avatar_url' => 'assets/media/avatars/300-3.jpg',
        ]);

        // Cyberdyne User
        User::create([
            'organization_id' => $orgCyberdyne->id,
            'name' => 'CyberDyne Admin',
            'email' => 'admin@cyberdyne.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'active',
            'avatar_url' => 'assets/media/avatars/300-4.jpg',
        ]);

        // 5. Categories
        $catAi = Category::create(['workspace_id' => $workspace->id, 'name' => 'AI Chat & Assistants', 'slug' => 'ai-chat', 'color' => '#6366F1', 'icon' => 'bi-robot']);
        $catDev = Category::create(['workspace_id' => $workspace->id, 'name' => 'API & Developer Tools', 'slug' => 'dev-tools', 'color' => '#10B981', 'icon' => 'bi-code-slash']);
        $catMedia = Category::create(['workspace_id' => $workspace->id, 'name' => 'Design & Generative Media', 'slug' => 'design-media', 'color' => '#EC4899', 'icon' => 'bi-palette']);
        $catInfra = Category::create(['workspace_id' => $workspace->id, 'name' => 'Hosting & Automation', 'slug' => 'hosting-automation', 'color' => '#F59E0B', 'icon' => 'bi-hdd-network']);

        // 6. Vendors
        $vOpenAI = Vendor::create(['workspace_id' => $workspace->id, 'name' => 'OpenAI', 'slug' => 'openai', 'website' => 'https://openai.com']);
        $vAnthropic = Vendor::create(['workspace_id' => $workspace->id, 'name' => 'Anthropic', 'slug' => 'anthropic', 'website' => 'https://anthropic.com']);
        $vMidjourney = Vendor::create(['workspace_id' => $workspace->id, 'name' => 'Midjourney', 'slug' => 'midjourney', 'website' => 'https://midjourney.com']);
        $vGithub = Vendor::create(['workspace_id' => $workspace->id, 'name' => 'GitHub', 'slug' => 'github', 'website' => 'https://github.com']);
        $vVercel = Vendor::create(['workspace_id' => $workspace->id, 'name' => 'Vercel', 'slug' => 'vercel', 'website' => 'https://vercel.com']);

        // 7. Tools
        $tChatGPT = Tool::create(['workspace_id' => $workspace->id, 'vendor_id' => $vOpenAI->id, 'category_id' => $catAi->id, 'name' => 'ChatGPT Plus', 'slug' => 'chatgpt-plus', 'description' => 'GPT-4o chat assistant subscription', 'is_ai_tool' => true]);
        $tClaude = Tool::create(['workspace_id' => $workspace->id, 'vendor_id' => $vAnthropic->id, 'category_id' => $catAi->id, 'name' => 'Claude Pro', 'slug' => 'claude-pro', 'description' => 'Claude 3.5 Sonnet pro assistant', 'is_ai_tool' => true]);
        $tOpenAiApi = Tool::create(['workspace_id' => $workspace->id, 'vendor_id' => $vOpenAI->id, 'category_id' => $catDev->id, 'name' => 'OpenAI API', 'slug' => 'openai-api', 'description' => 'Token API platform credit billing', 'is_ai_tool' => true]);
        $tMidjourney = Tool::create(['workspace_id' => $workspace->id, 'vendor_id' => $vMidjourney->id, 'category_id' => $catMedia->id, 'name' => 'Midjourney Pro', 'slug' => 'midjourney-pro', 'description' => 'Generative AI image generation suite', 'is_ai_tool' => true]);
        $tCopilot = Tool::create(['workspace_id' => $workspace->id, 'vendor_id' => $vGithub->id, 'category_id' => $catDev->id, 'name' => 'GitHub Copilot', 'slug' => 'github-copilot', 'description' => 'AI pair programmer', 'is_ai_tool' => true]);

        // 8. Payment Accounts
        $card1 = PaymentAccount::create([
            'organization_id' => $orgAcme->id,
            'workspace_id' => $workspace->id,
            'user_id' => $adminUser->id,
            'friendly_name' => 'Visa Sapphire Reserve',
            'type' => 'card',
            'provider_issuer' => 'Chase',
            'masked_identifier' => '•••• 4242',
            'billing_currency' => 'USD',
            'expiry_month' => 12,
            'expiry_year' => 2028,
            'status' => 'active',
            'spend_limit' => 1000.00,
        ]);

        $card2 = PaymentAccount::create([
            'organization_id' => $orgAcme->id,
            'workspace_id' => $workspace->id,
            'user_id' => $adminUser->id,
            'friendly_name' => 'Mastercard Business',
            'type' => 'card',
            'provider_issuer' => 'Citi',
            'masked_identifier' => '•••• 8890',
            'billing_currency' => 'USD',
            'expiry_month' => 8,
            'expiry_year' => 2026,
            'status' => 'expiring_soon',
            'spend_limit' => 500.00,
        ]);

        // 9. Meter Units
        $meterTokens = MeterUnit::create(['workspace_id' => $workspace->id, 'name' => 'Tokens', 'symbol' => '1k Tokens']);

        // 10. Subscriptions
        $today = Carbon::now();

        $sub1 = Subscription::create([
            'organization_id' => $orgAcme->id,
            'workspace_id' => $workspace->id,
            'tool_id' => $tChatGPT->id,
            'payment_account_id' => $card1->id,
            'owner_user_id' => $adminUser->id,
            'name' => 'ChatGPT Plus Monthly',
            'type' => 'monthly_recurring',
            'status' => 'active',
            'start_date' => $today->copy()->subMonths(3),
            'end_date' => $today->copy()->addDays(5),
            'billing_cadence_months' => 1,
        ]);
        PlanVersion::create([
            'subscription_id' => $sub1->id,
            'effective_start_date' => $sub1->start_date,
            'billing_currency' => 'USD',
            'recurring_amount' => 20.00,
            'normalized_monthly_amount' => 20.00,
        ]);

        $sub2 = Subscription::create([
            'organization_id' => $orgAcme->id,
            'workspace_id' => $workspace->id,
            'tool_id' => $tClaude->id,
            'payment_account_id' => $card1->id,
            'owner_user_id' => $adminUser->id,
            'name' => 'Claude Pro Monthly',
            'type' => 'monthly_recurring',
            'status' => 'active',
            'start_date' => $today->copy()->subMonths(2),
            'end_date' => $today->copy()->addDays(18),
            'billing_cadence_months' => 1,
        ]);
        PlanVersion::create([
            'subscription_id' => $sub2->id,
            'effective_start_date' => $sub2->start_date,
            'billing_currency' => 'USD',
            'recurring_amount' => 20.00,
            'normalized_monthly_amount' => 20.00,
        ]);

        $sub3 = Subscription::create([
            'organization_id' => $orgAcme->id,
            'workspace_id' => $workspace->id,
            'tool_id' => $tOpenAiApi->id,
            'payment_account_id' => $card2->id,
            'owner_user_id' => $adminUser->id,
            'name' => 'OpenAI API Token Package',
            'type' => 'prepaid_token',
            'status' => 'active',
            'start_date' => $today->copy()->subMonth(),
            'billing_cadence_months' => 1,
        ]);

        // 11. Projects
        $proj1 = Project::create([
            'organization_id' => $orgAcme->id,
            'workspace_id' => $workspace->id,
            'name' => 'AI Customer Support Agent',
            'client_name' => 'Acme Corp Support',
            'budget' => 250.00,
            'color' => '#6366F1',
            'is_tax_deductible' => true,
        ]);
        ProjectAllocation::create([
            'workspace_id' => $workspace->id,
            'project_id' => $proj1->id,
            'subscription_id' => $sub1->id,
            'allocation_percentage' => 100.00,
            'allocated_amount' => 20.00,
        ]);

        // 12. Targets & Alerts
        Target::create([
            'workspace_id' => $workspace->id,
            'name' => 'Monthly Total AI Spend Ceiling',
            'scope_type' => 'global',
            'period_type' => 'monthly',
            'target_amount' => 200.00,
            'currency' => 'USD',
            'basis' => 'forecast',
            'warning_threshold_pct' => 80,
            'critical_threshold_pct' => 100,
            'status' => 'active',
        ]);

        Alert::create([
            'workspace_id' => $workspace->id,
            'severity' => 'warning',
            'title' => 'Upcoming Renewal: ChatGPT Plus',
            'message' => 'Subscription ChatGPT Plus ($20.00) is scheduled to renew in 5 days on ' . $today->copy()->addDays(5)->format('M d, Y') . '.',
            'status' => 'unacknowledged',
        ]);
    }
}
