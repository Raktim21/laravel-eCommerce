<?php

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_unauthenticated_user_cannot_access_category_list()
    {
        $response = $this->get('/api/category');

        $response->assertStatus(401);
    }

    public function test_user_cannot_add_category()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->post('/api/admin/category-store',[
            'name' => 'test category',
            'slug' => 'test category slug',
        ]);

        $response->assertStatus(401);
    }

    public function test_user_cannot_update_category()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->put('/api/admin/category-update/1',[
            'name' => 'test category'
        ]);

        $response->assertStatus(405);
    }
}
