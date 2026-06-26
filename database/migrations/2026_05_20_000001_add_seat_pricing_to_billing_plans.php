<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('billing_plans', function (Blueprint $table) {
            $table->string('pricing_model')->default('flat')->after('trial_without_card');
            $table->integer('default_seats')->default(1)->after('pricing_model');
            $table->integer('seat_price')->default(0)->after('default_seats');
            $table->integer('max_seats')->nullable()->after('seat_price');
            $table->string('seat_type')->default('per_user')->after('max_seats');
        });
    }

    public function down(): void
    {
        Schema::table('billing_plans', function (Blueprint $table) {
            $table->dropColumn(['pricing_model', 'default_seats', 'seat_price', 'max_seats', 'seat_type']);
        });
    }
};
