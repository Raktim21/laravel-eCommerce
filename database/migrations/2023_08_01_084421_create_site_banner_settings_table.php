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
        Schema::create('site_banner_settings', function (Blueprint $table) {
            $table->id();
            $table->string('title', 100)->nullable();
            $table->string('subtitle', 100)->nullable();
            $table->string('button_text', 50)->nullable();
            $table->string('button_url', 100)->nullable();
            $table->string('image', 100)->nullable();
            $table->string('description', 500)->nullable();
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
        Schema::dropIfExists('site_banner_settings');
    }
};
