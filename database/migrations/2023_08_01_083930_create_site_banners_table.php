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
        Schema::create('site_banners', function (Blueprint $table) {
            $table->id();
            $table->string('flash_sale_image', 100)->nullable();
            $table->string('new_arrival_image1', 100)->nullable();
            $table->string('new_arrival_image2', 100)->nullable();
            $table->string('discount_product_image', 100)->nullable();
            $table->string('popular_product_image1', 100)->nullable();
            $table->string('popular_product_image2', 100)->nullable();
            $table->string('newsletter_image', 100)->nullable();
            $table->string('featured_banner_image', 100)->nullable();
            $table->string('all_product_side_image', 100)->nullable();
            $table->string('featured_product_image', 100)->nullable();
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
        Schema::dropIfExists('site_banners');
    }
};
