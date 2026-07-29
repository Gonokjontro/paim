<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workspaces', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('time_zone')->default('UTC');
            $table->string('base_currency', 3)->default('USD');
            $table->unsignedTinyInteger('fiscal_year_start_month')->default(1);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('workspace_id')->nullable()->after('id')->constrained('workspaces')->onDelete('cascade');
            $table->string('role')->default('admin')->after('email');
            $table->string('avatar_url')->nullable()->after('role');
            $table->json('preferences')->nullable()->after('avatar_url');
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained('workspaces')->onDelete('cascade');
            $table->string('name');
            $table->string('slug');
            $table->string('color', 20)->default('#4F46E5');
            $table->string('icon', 50)->default('bi-tag');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['workspace_id', 'slug']);
        });

        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained('workspaces')->onDelete('cascade');
            $table->string('name');
            $table->string('slug');
            $table->string('website')->nullable();
            $table->string('support_email')->nullable();
            $table->string('logo_url')->nullable();
            $table->timestamps();

            $table->unique(['workspace_id', 'slug']);
        });

        Schema::create('tools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained('workspaces')->onDelete('cascade');
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->onDelete('set null');
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->boolean('is_ai_tool')->default(true);
            $table->json('tags')->nullable();
            $table->string('status', 20)->default('active'); // active, inactive, evaluating
            $table->timestamps();

            $table->unique(['workspace_id', 'slug']);
        });

        Schema::create('payment_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained('workspaces')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('friendly_name');
            $table->string('type', 30)->default('card'); // card, bank, wallet, virtual_card, vendor_balance, invoice_manual, reimbursement, custom
            $table->string('provider_issuer')->nullable(); // Visa, Mastercard, Chase, PayPal, etc.
            $table->string('masked_identifier', 20)->nullable(); // e.g. 4242 or ending in 8890
            $table->string('billing_currency', 3)->default('USD');
            $table->unsignedTinyInteger('expiry_month')->nullable();
            $table->unsignedSmallInteger('expiry_year')->nullable();
            $table->string('status', 20)->default('active'); // active, expiring_soon, expired, inactive
            $table->decimal('spend_limit', 12, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_accounts');
        Schema::dropIfExists('tools');
        Schema::dropIfExists('vendors');
        Schema::dropIfExists('categories');
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['workspace_id']);
            $table->dropColumn(['workspace_id', 'role', 'avatar_url', 'preferences']);
        });
        Schema::dropIfExists('workspaces');
    }
};
