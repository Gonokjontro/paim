<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('client_name')->nullable();
            $table->decimal('budget', 12, 2)->default(0.00);
            $table->string('color')->default('#6366F1');
            $table->boolean('is_tax_deductible')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('project_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('allocation_percentage', 5, 2)->default(100.00);
            $table->decimal('allocated_amount', 12, 2)->default(0.00);
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_allocations');
        Schema::dropIfExists('projects');
    }
};
