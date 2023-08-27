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
        Schema::create('product_categories_sub', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('product_categories')->onDelete('restrict');
            $table->string('name', 50);
            $table->string('slug', 100)->unique();
            $table->string('image', 100)->nullable();
            $table->timestamps();

            $table->unique(['category_id', 'name']);
            $table->index(['category_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_categories_sub');
    }
};
