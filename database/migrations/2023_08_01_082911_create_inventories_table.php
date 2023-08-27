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
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_branch_id')->constrained('shop_branches')->onDelete('restrict');
            $table->foreignId('product_combination_id')->constrained('product_combinations')->onDelete('restrict');
            $table->integer('stock_quantity', false, true);
            $table->integer('damage_quantity', false, true)->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['shop_branch_id','product_combination_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inventories');
    }
};
