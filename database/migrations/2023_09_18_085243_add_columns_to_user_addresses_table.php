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
        if (!Schema::hasColumns('user_addresses', ['lat','lng'])) {
            Schema::table('user_addresses', function (Blueprint $table) {
                $table->string('lat', 50)->after('union_id');
                $table->string('lng', 50)->after('lat');
            });
        }
        if (!Schema::hasColumns('order_pickup_addresses', ['lat','lng'])) {
            Schema::table('order_pickup_addresses', function (Blueprint $table) {
                $table->string('lat', 50)->after('union_id');
                $table->string('lng', 50)->after('lat');
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
        Schema::table('user_addresses', function (Blueprint $table) {
            //
        });
    }
};
