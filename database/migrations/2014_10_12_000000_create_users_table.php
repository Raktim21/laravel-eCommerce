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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_branch_id')->nullable()->constrained('shop_branches')->onDelete('restrict');
            $table->string('username', 100)->unique()->comment('email');
            $table->string('password', 100);
            $table->string('salt', 100)->nullable();
            $table->string('name', 100);
            $table->string('phone', 30)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->string('password_reset_code', 10)->nullable();
            $table->string('password_reset_token', 150)->nullable();
            $table->timestamp('last_login')->nullable();
            $table->rememberToken();
            $table->string('google_id', 50)->nullable();
            $table->string('facebook_id', 50)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
