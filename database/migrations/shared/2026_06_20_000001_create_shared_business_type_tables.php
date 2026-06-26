<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('shared')->create('tenant_configs', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36);
            $table->string('business_type_slug', 100);
            $table->json('config');
            $table->string('config_hash', 64);
            $table->timestamps();

            $table->unique('tenant_id');
            $table->index(['tenant_id', 'business_type_slug']);
        });

        Schema::connection('shared')->create('tenant_module_overrides', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36);
            $table->string('module_slug', 100);
            $table->boolean('is_enabled')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'module_slug']);
        });
    }

    public function down(): void
    {
        Schema::connection('shared')->dropIfExists('tenant_module_overrides');
        Schema::connection('shared')->dropIfExists('tenant_configs');
    }
};
