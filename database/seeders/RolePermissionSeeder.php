<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
            'create/update/delete branch',
            'create/update/delete admin',
            'update pickup address',
            'create/update/delete user',
            'create/update/delete user addresses',
            'create/update/delete product categories',
            'create/update/delete product sub-categories',
            'create/update/delete product brands',
            'create/update/delete banner setting',
            'create/update/delete sponsors',
            'update general setting',
            'update delivery status',
            'get product data',
            'create/update/delete products',
            'update/transfer inventory stocks',
            'create/update promo-codes',
            'create/update orders',
            'update theme-setting',
            'create/update/delete expense categories',
            'create/update/delete expenses',
            'create/update/delete billing',
            'create/update/delete static content',
            'manage role',
            'manage inbox',
            'view/update seo setting',
        ];

        foreach ($permissions as $value) {
            Permission::create(['name' => $value,'guard_name' => 'admin-api']);
        }

        $role = ['Super Admin', 'Merchant', 'Customer'];

        foreach ($role as $value) {
            $role = Role::create(['name' => $value,'guard_name' => $value=='Customer' ? 'user-api' : 'admin-api']);

            if ($value == 'Super Admin') {
                $user = User::where('username', 'admin@admin.com')->first();
                $user->assignRole($role);
                $role->givePermissionTo(Permission::all());
            }
        }

    }
}
