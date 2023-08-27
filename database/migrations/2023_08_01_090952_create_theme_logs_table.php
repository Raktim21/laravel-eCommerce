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
        Schema::create('theme_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('theme_event_type_id')->constrained('theme_event_types')->onDelete('restrict');
            $table->foreignId('affected_theme_customizer_id')->constrained('theme_customizers')->onDelete('restrict');
            $table->tinyInteger('old_value', false, true)->nullable();
            $table->tinyInteger('old_order', false, true)->nullable();
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
        Schema::dropIfExists('theme_logs');
    }
};
