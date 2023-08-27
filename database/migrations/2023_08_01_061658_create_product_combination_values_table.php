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
        Schema::create('product_combination_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('combination_id')->constrained('product_combinations')->onDelete('cascade');
            $table->foreignId('att_value_id')->constrained('product_attribute_values')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['combination_id', 'att_value_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_combination_values');
    }
};
