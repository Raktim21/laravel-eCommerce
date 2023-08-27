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
        Schema::create('order_pickup_addresses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200)->unique();
            $table->string('phone', 30);
            $table->string('email', 100);
            $table->foreignId('upazila_id')->constrained('location_upazilas')->onDelete('restrict');
            $table->foreignId('union_id')->nullable()->constrained('location_unions')->onDelete('restrict');
            $table->string('postal_code', 50)->nullable();
            $table->string('address', 500);
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
        Schema::dropIfExists('order_pickup_addresses');
    }
};
