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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 50)->unique();
            $table->string('slug', 100)->unique();
            $table->string('name', 100);
            $table->string('short_description', 500)->nullable();
            $table->text('description');
            $table->string('thumbnail_image', 100);
            $table->string('featured_image', 100)->nullable();
            $table->foreignId('brand_id')->nullable()->constrained('product_brands')->onDelete('restrict');
            $table->foreignId('category_id')->constrained('product_categories')->onDelete('restrict');
            $table->foreignId('category_sub_id')->nullable()->constrained('product_categories_sub')->onDelete('restrict');
            $table->float('display_price', 8, 2, true);
            $table->float('previous_display_price', 8, 2, true)->nullable();
            $table->integer('view_count', false, true)->default(0);
            $table->integer('sold_count', false, true)->default(0);
            $table->integer('review_count', false, true)->default(0);
            $table->tinyInteger('is_on_sale', false, true)->default(0);
            $table->tinyInteger('is_featured', false, true)->default(0);
            $table->tinyInteger('status', false, true)->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
};
