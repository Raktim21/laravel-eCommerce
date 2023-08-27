<?php

namespace Tests\Unit;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_user_login()
    {
        $user = User::factory()->create([
            'email' => 'user123@user.com',
            'password' => bcrypt('user@123@'),
        ]);

        $response = $this->postJson('api/user/login', [
            'email' => $user->email,
            'password' => 'user@123@',
        ]);

        $response->assertStatus(200);
    }

//    public function test_admin_login()
//    {
//        $admin = Admin::factory()->create();
//
//        $response = $this->postJson('api/admin/login', [
//            'email' => $admin->email,
//            'password' => $admin->password,
//        ]);
//
//        $response->assertStatus(200);
//    }

}
