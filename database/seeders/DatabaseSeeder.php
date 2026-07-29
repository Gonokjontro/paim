<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
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
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Workspace
        $workspace = Workspace::create([
            'name' => 'Personal AI Workspace',
            'slug' => 'personal-ai',
            'time_zone' => 'UTC',
            'base_currency' => 'USD',
            'fiscal_year_start_month' => 1,
        ]);

        // 2. Demo Users for Each Role
        $adminUser = User::create([
            'workspace_id' => $workspace->id,
            'name' => 'Admin User',
            'email' => 'admin@paim.ai',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'active',
            'avatar_url' => 'assets/media/avatars/300-1.jpg',
        ]);

        $managerUser = User::create([
            'workspace_id' => $workspace->id,
            'name' => 'Manager User',
            'email' => 'manager@paim.ai',
            'password' => Hash::make('password'),
            'role' => 'manager',
            'status' => 'active',
            'avatar_url' => 'assets/media/avatars/300-2.jpg',
        ]);

        $viewerUser = User::create([
            'workspace_id' => $workspace->id,
            'name' => 'Viewer User (Read Only)',
            'email' => 'viewer@paim.ai',
            'password' => Hash::make('password'),
            'role' => 'viewer',
            'status' => 'active',
            'avatar_url' => 'assets/media/avatars/300-3.jpg',
        ]);

        // 3. Categories
        $catAi = Category::create(['workspace_id' => $workspace->id, 'name' => 'AI Chat & Assistants', 'slug' => 'ai-chat', 'color' => '#6366F1', 'icon' => 'bi-robot']);
        $catDev = Category::create(['workspace_id' => $workspace->id, 'name' => 'API & Developer Tools', 'slug' => 'dev-tools', 'color' => '#10B981', 'icon' => 'bi-code-slash']);
        $catMedia = Category::create(['workspace_id' => $workspace->id, 'name' => 'Design & Generative Media', 'slug' => 'design-media', 'color' => '#EC4899', 'icon' => 'bi-palette']);
        $catInfra = Category::create(['workspace_id' => $workspace->id, 'name' => 'Hosting & Automation', 'slug' => 'hosting-automation', 'color' => '#F59E0B', 'icon' => 'bi-hdd-network']);

        // 4. Vendors
        $vOpenAI = Vendor::create(['workspace_id' => $workspace->id, 'name' => 'OpenAI', 'slug' => 'openai', 'website' => 'https://openai.com']);
        $vAnthropic = Vendor::create(['workspace_id' => $workspace->id, 'name' => 'Anthropic', 'slug' => 'anthropic', 'website' => 'https://anthropic.com']);
        $vMidjourney = Vendor::create(['workspace_id' => $workspace->id, 'name' => 'Midjourney', 'slug' => 'midjourney', 'website' => 'https://midjourney.com']);
        $vGithub = Vendor::create(['workspace_id' => $workspace->id, 'name' => 'GitHub', 'slug' => 'github', 'website' => 'https://github.com']);
        $vVercel = Vendor::create(['workspace_id' => $workspace->id, 'name' => 'Vercel', 'slug' => 'vercel', 'website' => 'https://vercel.com']);

        // 5. Tools
        $tChatGPT = Tool::create(['workspace_id' => $workspace->id, 'vendor_id' => $vOpenAI->id, 'category_id' => $catAi->id, 'name' => 'ChatGPT Plus', 'slug' => 'chatgpt-plus', 'description' => 'GPT-4o chat assistant subscription', 'is_ai_tool' => true]);
        $tClaude = Tool::create(['workspace_id' => $workspace->id, 'vendor_id' => $vAnthropic->id, 'category_id' => $catAi->id, 'name' => 'Claude Pro', 'slug' => 'claude-pro', 'description' => 'Claude 3.5 Sonnet pro assistant', 'is_ai_tool' => true]);
        $tOpenAiApi = Tool::create(['workspace_id' => $workspace->id, 'vendor_id' => $vOpenAI->id, 'category_id' => $catDev->id, 'name' => 'OpenAI API', 'slug' => 'openai-api', 'description' => 'Token API platform credit billing', 'is_ai_tool' => true]);
        $tMidjourney = Tool::create(['workspace_id' => $workspace->id, 'vendor_id' => $vMidjourney->id, 'category_id' => $catMedia->id, 'name' => 'Midjourney Pro', 'slug' => 'midjourney-pro', 'description' => 'Generative AI image generation suite', 'is_ai_tool' => true]);
        $tCopilot = Tool::create(['workspace_id' => $workspace->id, 'vendor_id' => $vGithub->id, 'category_id' => $catDev->id, 'name' => 'GitHub Copilot', 'slug' => 'github-copilot', 'description' => 'AI pair programmer', 'is_ai_tool' => true]);
        $tVercel = Tool::create(['workspace_id' => $workspace->id, 'vendor_id' => $vVercel->id, 'category_id' => $catInfra->id, 'name' => 'Vercel Pro', 'slug' => 'vercel-pro', 'description' => 'Frontend cloud platform', 'is_ai_tool' => false]);

        // 6. Payment Accounts
        $card1 = PaymentAccount::create([
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

        // 7. Meter Units
        $meterTokens = MeterUnit::create(['workspace_id' => $workspace->id, 'name' => 'Tokens', 'symbol' => '1k Tokens']);
        $meterImages = MeterUnit::create(['workspace_id' => $workspace->id, 'name' => 'Images', 'symbol' => 'Images']);

        // 8. Subscriptions
        $today = Carbon::now();

        // Sub 1: ChatGPT Plus ($20/mo)
        $sub1 = Subscription::create([
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
        CostEntry::create([
            'workspace_id' => $workspace->id,
            'subscription_id' => $sub1->id,
            'payment_account_id' => $card1->id,
            'entry_type' => 'recurring_fee',
            'posted_date' => $today->copy()->subDays(25),
            'original_amount' => 20.00,
            'base_amount' => 20.00,
            'status' => 'posted',
            'description' => 'ChatGPT Plus Monthly Subscription Fee',
        ]);

        // Sub 2: Claude Pro ($20/mo)
        $sub2 = Subscription::create([
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
        CostEntry::create([
            'workspace_id' => $workspace->id,
            'subscription_id' => $sub2->id,
            'payment_account_id' => $card1->id,
            'entry_type' => 'recurring_fee',
            'posted_date' => $today->copy()->subDays(12),
            'original_amount' => 20.00,
            'base_amount' => 20.00,
            'status' => 'posted',
            'description' => 'Claude Pro Monthly Subscription Fee',
        ]);

        // Sub 3: OpenAI API (Prepaid Lot $100)
        $sub3 = Subscription::create([
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
        $pkg = TokenPackage::create([
            'workspace_id' => $workspace->id,
            'subscription_id' => $sub3->id,
            'meter_unit_id' => $meterTokens->id,
            'package_name' => '5M Token Credit Pack',
            'purchase_cost' => 100.00,
            'currency' => 'USD',
            'granted_units' => 5000.0,
            'consumed_units' => 1850.0,
            'remaining_units' => 3150.0,
            'purchase_date' => $today->copy()->subMonth(),
            'expiry_date' => $today->copy()->addMonths(5),
            'status' => 'active',
        ]);
        UsageEntry::create([
            'workspace_id' => $workspace->id,
            'subscription_id' => $sub3->id,
            'meter_unit_id' => $meterTokens->id,
            'model_name' => 'gpt-4o',
            'environment_project' => 'production-api',
            'usage_date' => $today->copy()->subDays(2),
            'unit_count' => 1850.0,
            'calculated_cost' => 37.00,
            'currency' => 'USD',
            'provider_reference' => 'USAGE-REQ-9921',
        ]);

        // Sub 4: Midjourney Pro ($60/mo annual)
        $sub4 = Subscription::create([
            'workspace_id' => $workspace->id,
            'tool_id' => $tMidjourney->id,
            'payment_account_id' => $card1->id,
            'owner_user_id' => $adminUser->id,
            'name' => 'Midjourney Pro Plan',
            'type' => 'annual_recurring',
            'status' => 'active',
            'start_date' => $today->copy()->subMonths(6),
            'end_date' => $today->copy()->addMonths(6),
            'billing_cadence_months' => 12,
        ]);
        PlanVersion::create([
            'subscription_id' => $sub4->id,
            'effective_start_date' => $sub4->start_date,
            'billing_currency' => 'USD',
            'recurring_amount' => 720.00,
            'normalized_monthly_amount' => 60.00,
        ]);

        // Sub 5: GitHub Copilot ($10/mo)
        $sub5 = Subscription::create([
            'workspace_id' => $workspace->id,
            'tool_id' => $tCopilot->id,
            'payment_account_id' => $card1->id,
            'owner_user_id' => $adminUser->id,
            'name' => 'GitHub Copilot Individual',
            'type' => 'monthly_recurring',
            'status' => 'active',
            'start_date' => $today->copy()->subMonths(4),
            'end_date' => $today->copy()->addDays(14),
            'billing_cadence_months' => 1,
        ]);
        PlanVersion::create([
            'subscription_id' => $sub5->id,
            'effective_start_date' => $sub5->start_date,
            'billing_currency' => 'USD',
            'recurring_amount' => 10.00,
            'normalized_monthly_amount' => 10.00,
        ]);

        // 9. Targets
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

        // 10. Alerts
        Alert::create([
            'workspace_id' => $workspace->id,
            'severity' => 'warning',
            'title' => 'Upcoming Renewal: ChatGPT Plus',
            'message' => 'Subscription ChatGPT Plus ($20.00) is scheduled to renew in 5 days on ' . $today->copy()->addDays(5)->format('M d, Y') . '.',
            'status' => 'unacknowledged',
        ]);

        Alert::create([
            'workspace_id' => $workspace->id,
            'severity' => 'critical',
            'title' => 'Payment Card Expiring Soon',
            'message' => 'Mastercard Business (•••• 8890) attached to OpenAI API expires 08/2026.',
            'status' => 'unacknowledged',
        ]);
    }
}
