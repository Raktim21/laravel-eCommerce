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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('restrict');
            $table->foreignId('product_combination_id')->constrained('product_combinations')->onDelete('restrict');
            $table->integer('product_quantity', false, true);
            $table->float('product_price', 8, 2, true);
            $table->float('total_price', 8, 2, true);
            $table->tinyInteger('is_reviewed', false, true)->default(0);
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
        Schema::dropIfExists('order_items');
    }
};
