<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 100)->unique();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->string('version', 20)->default('1.0.0');
            $table->string('provider_class', 255)->nullable();
            $table->json('dependencies')->nullable();
            $table->json('required_features')->nullable();
            $table->boolean('is_core')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
