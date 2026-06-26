<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('billing_plans')->where('slug', 'free')->update([
            'pricing_model' => 'flat',
            'default_seats' => 0,
            'seat_price' => 0,
            'max_seats' => null,
        ]);

        DB::table('billing_plans')->where('slug', 'starter')->update([
            'pricing_model' => 'per_seat',
            'default_seats' => 1,
            'seat_price' => 500,
            'max_seats' => 10,
        ]);

        DB::table('billing_plans')->where('slug', 'professional')->update([
            'pricing_model' => 'per_seat',
            'default_seats' => 3,
            'seat_price' => 500,
            'max_seats' => 25,
        ]);

        DB::table('billing_plans')->where('slug', 'enterprise')->update([
            'pricing_model' => 'per_seat',
            'default_seats' => 5,
            'seat_price' => 350,
            'max_seats' => null,
        ]);
    }

    public function down(): void
    {
        DB::table('billing_plans')->where('slug', 'free')->update([
            'pricing_model' => 'flat',
            'default_seats' => 1,
            'seat_price' => 0,
            'max_seats' => null,
        ]);

        DB::table('billing_plans')->where('slug', 'starter')->update([
            'pricing_model' => 'flat',
            'default_seats' => 1,
            'seat_price' => 0,
            'max_seats' => null,
        ]);

        DB::table('billing_plans')->where('slug', 'professional')->update([
            'pricing_model' => 'flat',
            'default_seats' => 1,
            'seat_price' => 0,
            'max_seats' => null,
        ]);

        DB::table('billing_plans')->where('slug', 'enterprise')->update([
            'pricing_model' => 'flat',
            'default_seats' => 1,
            'seat_price' => 0,
            'max_seats' => null,
        ]);
    }
};
