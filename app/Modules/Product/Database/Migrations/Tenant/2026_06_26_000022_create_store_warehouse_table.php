<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_warehouse', function (Blueprint $table) {
            $table->string('store_id', 26);
            $table->unsignedBigInteger('warehouse_id');
            $table->boolean('is_default_for_receiving')->default(false);
            $table->boolean('is_default_for_fulfillment')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->primary(['store_id', 'warehouse_id']);
            $table->index('warehouse_id');

            $table->foreign('store_id')->references('id')->on('stores')->cascadeOnDelete();
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->cascadeOnDelete();
        });

        // Auto-link existing warehouses to the default store
        $defaultStore = DB::table('stores')->where('is_default', true)->first();
        if ($defaultStore) {
            $warehouses = DB::table('warehouses')->get();
            foreach ($warehouses as $warehouse) {
                DB::table('store_warehouse')->insert([
                    'store_id' => $defaultStore->id,
                    'warehouse_id' => $warehouse->id,
                    'is_default_for_receiving' => $warehouse->is_default,
                    'is_default_for_fulfillment' => $warehouse->is_default,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('store_warehouse');
    }
};
