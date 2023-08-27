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
        Schema::create('billing_carts', function (Blueprint $table) {
            $table->id();
            $table->string('billing_number', 36)->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('restrict');
            $table->foreignId('billing_cart_customers_id')->nullable()->constrained('billing_cart_customers')->onDelete('restrict');
            $table->float('discount_amount', 8, 2, true)->default(0.00);
            $table->string('remarks', 500)->nullable();
            $table->tinyInteger('is_follow_up', false, true)->default(0);
            $table->tinyInteger('is_ordered', false, true)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('billing_carts');
    }
};
