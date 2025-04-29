<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('base_sub_cost', 12, 4)->nullable();
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('base_cost', 12, 4)->nullable();
            $table->decimal('base_total_cost', 12, 4)->nullable();
        });

        Schema::table('cart', function (Blueprint $table) {
            $table->decimal('base_sub_cost', 12, 4)->nullable();
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->decimal('base_cost', 12, 4)->nullable();
            $table->decimal('base_total_cost', 12, 4)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('base_sub_cost');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('base_cost');
            $table->dropColumn('base_total_cost');
        });

        Schema::table('cart', function (Blueprint $table) {
            $table->dropColumn('base_sub_cost');
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropColumn('base_cost');
            $table->dropColumn('base_total_cost');
        });
    }
};
