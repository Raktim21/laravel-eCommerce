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
        Schema::table('site_general_settings', function (Blueprint $table) {
            $table->dropColumn('theme_color');
            $table->dropColumn('button_color');
            $table->dropColumn('button_text_color');
            $table->dropColumn('price_color');
            $table->dropColumn('discount_price_color');
            $table->dropColumn('text_color');
            $table->dropColumn('badge_background_color');
            $table->dropColumn('badge_text_color');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('site_general_settings', function (Blueprint $table) {
            //
        });
    }
};
