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
        Schema::create('location_districts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('division_id')->constrained('location_divisions')->onDelete('restrict');
            $table->string('name', 50);
            $table->string('local_name', 100)->nullable();
            $table->string('lat', 20)->nullable();
            $table->string('lon', 20)->nullable();
            $table->string('url', 100)->nullable();
            $table->timestamps();

            $table->unique(['division_id','name'],'location_districts_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('location_districts');
    }
};
