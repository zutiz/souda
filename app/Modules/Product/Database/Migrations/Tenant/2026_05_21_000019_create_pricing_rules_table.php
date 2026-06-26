<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('type', 20);
            $table->string('scope', 20);
            $table->unsignedBigInteger('scope_id')->nullable();
            $table->string('condition_type', 30)->nullable();
            $table->json('condition_value')->nullable();
            $table->unsignedInteger('discount_value');
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('priority')->default(0);
            $table->unsignedInteger('max_uses')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->timestamps();

            $table->index(['scope', 'scope_id'], 'idx_pr_scope');
            $table->index('is_active', 'idx_pr_active');
            $table->index(['start_at', 'end_at'], 'idx_pr_dates');
            $table->index('priority', 'idx_pr_priority');
            $table->index(['is_active', 'scope', 'scope_id', 'start_at', 'end_at'], 'idx_pr_active_scope_dates');
            $table->index(['is_active', 'priority', 'start_at'], 'idx_pr_active_priority');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_rules');
    }
};
