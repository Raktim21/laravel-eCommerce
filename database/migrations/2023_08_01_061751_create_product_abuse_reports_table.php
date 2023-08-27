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
        Schema::create('product_abuse_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('guest_session_id', 50)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('phone_no', 30)->nullable();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('complaint_motes', 500);
            $table->tinyInteger('is_checked', false, true)->default(0);
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
        Schema::dropIfExists('product_abuse_reports');
    }
};
