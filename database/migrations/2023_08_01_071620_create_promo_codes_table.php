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
        Schema::create('promo_codes', function (Blueprint $table) {
            $table->id();
            $table->string('title', 100);
            $table->string('code', 100)->unique();
            $table->tinyInteger('is_active', false, true)->default(1);
            $table->tinyInteger('is_global_user', false, true)->default(0);
            $table->tinyInteger('is_global_product', false, true)->default(0);
            $table->tinyInteger('is_percentage', false, true);
            $table->float('discount', 8, 2, true);
            $table->integer('max_usage', false, true)->default(0)->comment('per-user-usage-limit');
            $table->integer('max_num_users', false, true)->default(0);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->timestamps();

            $table->index('code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('promo_codes');
    }
};
