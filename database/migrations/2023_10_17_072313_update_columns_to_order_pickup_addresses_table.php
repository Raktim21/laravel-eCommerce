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
        Schema::table('order_pickup_addresses', function (Blueprint $table) {
            $table->integer('hub_id')->nullable()->after('address');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('name', 150)->change();
            $table->string('slug', 250)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_pickup_addresses', function (Blueprint $table) {
            //
        });
    }
};
