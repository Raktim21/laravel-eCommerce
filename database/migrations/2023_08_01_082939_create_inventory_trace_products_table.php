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
        Schema::create('inventory_trace_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trace_id')->constrained('inventory_traces')->onDelete('restrict');
            $table->foreignId('product_combination_id')->constrained('product_combinations')->onDelete('restrict');
            $table->integer('product_quantity', false, true);
            $table->timestamps();

            $table->unique(['trace_id','product_combination_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inventory_trace_products');
    }
};
