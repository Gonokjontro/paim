<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained('workspaces')->onDelete('cascade');
            $table->string('name');
            $table->string('scope_type', 30)->default('global'); // global, category, tool, subscription, payment_account
            $table->unsignedBigInteger('scope_id')->nullable();
            $table->string('period_type', 20)->default('monthly'); // monthly, quarterly, annual, rolling, one_time
            $table->decimal('target_amount', 12, 4);
            $table->string('currency', 3)->default('USD');
            $table->string('basis', 30)->default('forecast'); // actual, committed, forecast, actual_plus_committed
            $table->unsignedTinyInteger('warning_threshold_pct')->default(80);
            $table->unsignedTinyInteger('critical_threshold_pct')->default(100);
            $table->string('status', 20)->default('active');
            $table->timestamps();
        });

        Schema::create('alert_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained('workspaces')->onDelete('cascade');
            $table->string('name');
            $table->string('event_type', 40); // target_warning, target_critical, renewal_imminent, trial_expiring, payment_expiring, stale_usage, unusual_spike
            $table->string('scope_type', 30)->nullable();
            $table->unsignedBigInteger('scope_id')->nullable();
            $table->decimal('threshold_value', 12, 4)->nullable();
            $table->unsignedInteger('cool_down_hours')->default(24);
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });

        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained('workspaces')->onDelete('cascade');
            $table->foreignId('alert_policy_id')->nullable()->constrained('alert_policies')->onDelete('set null');
            $table->string('severity', 20)->default('info'); // info, warning, critical
            $table->string('title');
            $table->text('message');
            $table->string('status', 20)->default('unacknowledged'); // unacknowledged, acknowledged, snoozed, resolved, dismissed
            $table->timestamp('snoozed_until')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->foreignId('acknowledged_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('import_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained('workspaces')->onDelete('cascade');
            $table->string('file_name');
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('imported_rows')->default(0);
            $table->unsignedInteger('failed_rows')->default(0);
            $table->string('status', 20)->default('pending'); // pending, completed, failed, rolled_back
            $table->json('error_summary')->nullable();
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained('workspaces')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('event_type', 50);
            $table->string('entity_type', 50);
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->json('before_state')->nullable();
            $table->json('after_state')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('import_batches');
        Schema::dropIfExists('alerts');
        Schema::dropIfExists('alert_policies');
        Schema::dropIfExists('targets');
    }
};
