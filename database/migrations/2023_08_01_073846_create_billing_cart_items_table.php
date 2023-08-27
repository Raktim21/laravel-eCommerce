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
        Schema::create('billing_cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('billing_cart_id')->constrained('billing_carts')->onDelete('restrict');
            $table->foreignId('product_combination_id')->constrained('product_combinations')->onDelete('restrict');
            $table->integer('product_quantity', false, true);
            $table->timestamps();

            $table->unique(['billing_cart_id','product_combination_id']);
            $table->index(['billing_cart_id','product_combination_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('billing_cart_items');
    }
};
