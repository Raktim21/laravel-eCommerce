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
        Schema::create('theme_customizers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->integer('value', false, true);
            $table->tinyInteger('is_active', false, true)->default(1);
            $table->tinyInteger('is_inactivable', false, true)->default(1);
            $table->tinyInteger('is_static_position', false, true)->default(0);
            $table->integer('ordering', false, true);
            $table->integer('items', false, true)->default(0);
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
        Schema::dropIfExists('theme_customizers');
    }
};
