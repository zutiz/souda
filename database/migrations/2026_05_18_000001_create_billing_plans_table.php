<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->integer('monthly_price')->default(0);
            $table->integer('yearly_price')->nullable();
            $table->string('currency', 3)->default('BDT');
            $table->json('features')->nullable();
            $table->json('limits')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);
            $table->boolean('popular')->default(false);
            $table->string('cta')->nullable();
            $table->boolean('trial_enabled')->default(false);
            $table->integer('trial_days')->default(0);
            $table->boolean('trial_without_card')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_plans');
    }
};
