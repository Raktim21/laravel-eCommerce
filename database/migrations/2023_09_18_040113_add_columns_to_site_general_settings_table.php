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
        if (Schema::hasColumn('site_general_settings', 'delivery_status')) {
            Schema::table('site_general_settings', function (Blueprint $table) {
                $table->dropColumn('delivery_status');
            });
        }
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
