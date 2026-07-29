<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meter_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained('workspaces')->onDelete('cascade');
            $table->string('name');
            $table->string('symbol', 20); // e.g. tokens, requests, images, minutes, credits
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('rate_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained('subscriptions')->onDelete('cascade');
            $table->foreignId('meter_unit_id')->constrained('meter_units')->onDelete('cascade');
            $table->decimal('tier_min_units', 18, 4)->default(0.0000);
            $table->decimal('tier_max_units', 18, 4)->nullable(); // null means infinity
            $table->decimal('unit_price', 18, 6)->default(0.000000);
            $table->string('currency', 3)->default('USD');
            $table->date('effective_date');
            $table->timestamps();
        });

        Schema::create('token_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained('workspaces')->onDelete('cascade');
            $table->foreignId('subscription_id')->constrained('subscriptions')->onDelete('cascade');
            $table->foreignId('meter_unit_id')->constrained('meter_units')->onDelete('cascade');
            $table->string('package_name');
            $table->decimal('purchase_cost', 12, 4);
            $table->string('currency', 3)->default('USD');
            $table->decimal('granted_units', 18, 4);
            $table->decimal('consumed_units', 18, 4)->default(0.0000);
            $table->decimal('remaining_units', 18, 4);
            $table->date('purchase_date');
            $table->date('expiry_date')->nullable();
            $table->boolean('allow_carryover')->default(false);
            $table->string('status', 20)->default('active'); // active, exhausted, expired
            $table->timestamps();
        });

        Schema::create('usage_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained('workspaces')->onDelete('cascade');
            $table->foreignId('subscription_id')->constrained('subscriptions')->onDelete('cascade');
            $table->foreignId('meter_unit_id')->constrained('meter_units')->onDelete('cascade');
            $table->string('model_name')->nullable(); // e.g. gpt-4o, claude-3-5-sonnet
            $table->string('environment_project')->nullable(); // e.g. production, dev, research
            $table->date('usage_date');
            $table->decimal('unit_count', 18, 4);
            $table->decimal('calculated_cost', 12, 4)->default(0.0000);
            $table->string('currency', 3)->default('USD');
            $table->string('provider_reference')->nullable();
            $table->timestamps();
        });

        Schema::create('usage_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usage_entry_id')->constrained('usage_entries')->onDelete('cascade');
            $table->foreignId('token_package_id')->constrained('token_packages')->onDelete('cascade');
            $table->decimal('allocated_units', 18, 4);
            $table->decimal('effective_cost', 12, 4)->default(0.0000);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usage_allocations');
        Schema::dropIfExists('usage_entries');
        Schema::dropIfExists('token_packages');
        Schema::dropIfExists('rate_tiers');
        Schema::dropIfExists('meter_units');
    }
};
