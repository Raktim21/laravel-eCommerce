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
        Schema::create('static_menus', function (Blueprint $table) {
            $table->id();
            $table->string('menu_name', 200)->unique();
            $table->foreignId('parent_menu_id')->nullable()->constrained('static_menus')->onDelete('restrict');
            $table->foreignId('static_contents_id')->constrained('static_contents')->onDelete('restrict');
            $table->foreignId('static_menu_type_id')->constrained('static_menu_types')->onDelete('restrict');
            $table->tinyInteger('is_changeable', false, true)->default(1);
            $table->tinyInteger('status', false, true)->default(1)->comment('0 = Inactive, 1 = Active');
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
        Schema::dropIfExists('static_menus');
    }
};
