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
        Schema::create('site_general_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dashboard_language_id')->default(1)->constrained('dashboard_languages')->onDelete('restrict');
            $table->foreignId('currency_id')->default(1)->constrained('currencies')->onDelete('restrict');
            $table->string('name', 100);
            $table->string('logo', 100);
            $table->string('dark_logo', 100)->nullable();
            $table->string('favicon', 100);
            $table->string('email', 100)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('address', 200)->nullable();
            $table->string('facebook_page_id', 30)->nullable();
            $table->string('facebook', 100)->nullable();
            $table->string('twitter', 100)->nullable();
            $table->string('linkedin', 100)->nullable();
            $table->string('instagram', 100)->nullable();
            $table->string('youtube', 100)->nullable();
            $table->string('google', 100)->nullable();
            $table->string('pinterest', 100)->nullable();
            $table->string('about', 500)->nullable();
            $table->string('theme_color', 20)->default('rgb(5, 191, 133)');
            $table->string('button_color', 20)->default('#000000');
            $table->string('button_text_color', 20)->default('#ffffff');
            $table->string('price_color', 20)->default('#ff0062');
            $table->string('discount_price_color', 20)->default('black');
            $table->string('text_color', 20)->default('#000000');
            $table->string('badge_background_color', 20)->default('#765eff');
            $table->string('badge_text_color', 20)->default('#ffffff');
            $table->tinyInteger('delivery_status', false, true)->default(0);
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
        Schema::dropIfExists('site_general_settings');
    }
};
