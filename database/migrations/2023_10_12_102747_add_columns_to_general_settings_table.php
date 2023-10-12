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
            $table->integer('pickup_unique_id', false, true)->unique()->after('id');
            $table->unsignedBigInteger('shop_branch_id')->default(1)->after('pickup_unique_id');
            $table->foreign('shop_branch_id')
                ->references('id')->on('shop_branches');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('general_settings', function (Blueprint $table) {
            //
        });
    }
};
