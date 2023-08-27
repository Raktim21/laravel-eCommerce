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
        Schema::create('customer_carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('guest_session_id', 50)->nullable();
            $table->foreignId('product_combination_id')->constrained('product_combinations')->onDelete('restrict');
            $table->integer('product_quantity', false, true)->default(1);
            $table->timestamps();

            $table->unique(['user_id','product_combination_id']);
            $table->unique(['guest_session_id','product_combination_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customer_carts');
    }
};
