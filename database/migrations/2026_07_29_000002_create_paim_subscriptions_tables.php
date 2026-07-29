<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained('workspaces')->onDelete('cascade');
            $table->foreignId('tool_id')->constrained('tools')->onDelete('cascade');
            $table->foreignId('payment_account_id')->nullable()->constrained('payment_accounts')->onDelete('set null');
            $table->foreignId('owner_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('name');
            $table->string('type', 30)->default('monthly_recurring'); // free, trial, monthly_recurring, annual_recurring, other_recurring, prepaid_token, on_demand, hybrid, one_time
            $table->string('status', 20)->default('active'); // active, trial, paused, pending_cancel, cancelled, expired
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->date('cancellation_deadline')->nullable();
            $table->date('access_end_date')->nullable();
            $table->boolean('auto_renew')->default(true);
            $table->unsignedInteger('billing_cadence_months')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('plan_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained('subscriptions')->onDelete('cascade');
            $table->date('effective_start_date');
            $table->date('effective_end_date')->nullable();
            $table->string('billing_currency', 3)->default('USD');
            $table->decimal('recurring_amount', 12, 4)->default(0.0000);
            $table->decimal('normalized_monthly_amount', 12, 4)->default(0.0000);
            $table->decimal('tax_rate', 5, 2)->default(0.00);
            $table->decimal('discount_amount', 12, 4)->default(0.0000);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('cost_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_version_id')->constrained('plan_versions')->onDelete('cascade');
            $table->string('name');
            $table->string('type', 30)->default('base_fee'); // base_fee, seat, add_on, usage, tax, surcharge, discount
            $table->decimal('amount', 12, 4)->default(0.0000);
            $table->string('currency', 3)->default('USD');
            $table->timestamps();
        });

        Schema::create('cost_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained('workspaces')->onDelete('cascade');
            $table->foreignId('subscription_id')->constrained('subscriptions')->onDelete('cascade');
            $table->foreignId('payment_account_id')->nullable()->constrained('payment_accounts')->onDelete('set null');
            $table->string('entry_type', 30)->default('recurring_fee'); // recurring_fee, usage_charge, token_purchase, tax, discount, credit, refund, adjustment, reversal
            $table->date('posted_date');
            $table->decimal('original_amount', 12, 4);
            $table->string('original_currency', 3)->default('USD');
            $table->decimal('base_amount', 12, 4);
            $table->decimal('fx_rate', 12, 6)->default(1.000000);
            $table->string('status', 20)->default('posted'); // posted, draft, void
            $table->string('reference_number')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('reversal_of_entry_id')->nullable()->constrained('cost_entries')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('expected_commitments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained('workspaces')->onDelete('cascade');
            $table->foreignId('subscription_id')->constrained('subscriptions')->onDelete('cascade');
            $table->foreignId('plan_version_id')->nullable()->constrained('plan_versions')->onDelete('set null');
            $table->date('due_date');
            $table->decimal('expected_amount', 12, 4);
            $table->string('currency', 3)->default('USD');
            $table->foreignId('matching_cost_entry_id')->nullable()->constrained('cost_entries')->onDelete('set null');
            $table->string('status', 20)->default('scheduled'); // scheduled, matched, overdue, cancelled
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expected_commitments');
        Schema::dropIfExists('cost_entries');
        Schema::dropIfExists('cost_components');
        Schema::dropIfExists('plan_versions');
        Schema::dropIfExists('subscriptions');
    }
};
