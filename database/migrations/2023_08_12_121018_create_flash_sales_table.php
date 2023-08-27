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
        Schema::create('flash_sales', function (Blueprint $table) {
            $table->id();
            $table->string('title', 150);
            $table->string('short_description',400)->nullable();
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->string('image')->nullable();
            $table->float('discount', 8, 2, true)->comment('In Percentage');
            $table->tinyInteger('status', false, true)->default(1);
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
        Schema::dropIfExists('flash_sales');
    }
};
