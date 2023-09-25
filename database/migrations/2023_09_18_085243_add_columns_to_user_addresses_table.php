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
        Schema::table('user_addresses', function (Blueprint $table) {
            $table->string('lat', 20)->after('union_id');
            $table->string('lng', 20)->after('lat');
        });

        Schema::table('order_pickup_addresses', function (Blueprint $table) {
            $table->string('lat', 20)->after('union_id');
            $table->string('lng', 20)->after('lat');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_addresses', function (Blueprint $table) {
            //
        });
    }
};
