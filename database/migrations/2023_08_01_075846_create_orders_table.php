<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_branch_id')->nullable()->constrained('shop_branches')->onDelete('restrict');
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->string('order_number', 50)->unique();
            $table->foreignId('order_status_id')->default(1)->constrained('order_statuses')->onDelete('restrict');
            $table->foreignId('order_status_updated_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->foreignId('payment_method_id')->default(1)->constrained('order_payment_methods')->onDelete('restrict');
            $table->foreignId('delivery_method_id')->constrained('order_delivery_methods')->onDelete('restrict');
            $table->foreignId('delivery_system_id')->nullable()->constrained('order_delivery_systems')->onDelete('restrict');
            $table->foreignId('delivery_address_id')->nullable()->constrained('user_addresses')->onDelete('restrict');
            $table->string('delivery_tracking_number', 100)->nullable();
            $table->float('delivery_cost', 8, 2, true)->default(0.00);
            $table->string('delivery_status')->nullable();
            $table->string('delivery_remarks', 500)->nullable();
            $table->string('merchant_remarks', 500)->nullable();
            $table->foreignId('promo_code_id')->nullable()->constrained('promo_codes')->onDelete('restrict');
            $table->float('promo_discount', 8, 2, true)->default(0.00);
            $table->float('additional_charges', 8, 2, true)->default(0.00);
            $table->float('sub_total_amount', 8, 2, true)->default(0.00);
            $table->float('total_amount', 8, 2, true)->default(0.00);
            $table->float('paid_amount', 8, 2, true)->default(0.00);
            $table->foreignId('payment_status_id')->constrained('order_payment_statuses')->onDelete('restrict');
            $table->timestamps();

            $table->index('order_number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
