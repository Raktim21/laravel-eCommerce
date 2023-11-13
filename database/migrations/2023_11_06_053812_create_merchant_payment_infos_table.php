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
        Schema::create('merchant_payment_infos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_system_id')->constrained('order_delivery_systems')->onDelete('cascade');
            $table->foreignId('payment_method_id')->constrained('merchant_payment_methods')->onDelete('cascade');
            $table->foreignId('bank_branch_id')->nullable()->constrained('bank_branches')->onDelete('restrict');
            $table->string('bank_account_holder', 100)->nullable();
            $table->string('bank_account_no', 30)->nullable();
            $table->string('bkash_no', 20)->nullable();
            $table->string('rocket_no', 20)->nullable();
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
        Schema::dropIfExists('merchant_payment_infos');
    }
};
