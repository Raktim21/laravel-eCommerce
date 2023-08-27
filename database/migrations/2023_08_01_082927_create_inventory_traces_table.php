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
        Schema::create('inventory_traces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_branch_id')->constrained('shop_branches')->onDelete('restrict');
            $table->foreignId('to_branch_id')->constrained('shop_branches')->onDelete('restrict');
            $table->date('event_date');
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
        Schema::dropIfExists('inventory_traces');
    }
};
