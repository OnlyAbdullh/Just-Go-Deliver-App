<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolePermessionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = ['user', 'manager', 'store_admin'];

        $managerPermessions = [
            'add permession',
            'add role',
            'revoke role',
            'revoke permession',
            'assign role',
            'assign permession',
            'view users'
        ];

        $userPermessions = [
            'add to cart',
            'delete from cart',
            'modify cart',
            'create order',
            'delete order',
            'modify order',
            'add favorite',
            'remove favorite',
            'rate product',
            'update profile',
            'track order'
        ];

        $storeAdminPermessions = [
            'create store',
            'update store',
            'delete store',
            'create product',
            'update product',
            'delete product',
            'update stock',
            'accept order',
            'reject order',
            'modify order status',
            'view sales',
            'view orders',
        ];

        $allPermessions = array_merge($managerPermessions, $userPermessions, $storeAdminPermessions);

        foreach ($allPermessions as $permession) {
            Permission::firstOrCreate(['name' => $permession, 'guard_name' => 'api']);
        }


        foreach ($roles as $roleName) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'api']);

            if ($roleName === 'manager') {
                $role->syncPermissions($managerPermessions);
            }
            if ($roleName === 'user') {
                $role->syncPermissions($userPermessions);
            }
            if ($roleName === 'store_admin') {
                $storeAdminPermessions = array_merge($userPermessions, $storeAdminPermessions);
                $role->syncPermissions($storeAdminPermessions);
            }
        }

        $manager = User::create([
            'first_name' => 'Hasan',
            'last_name' => 'Zaeter',
            'location' => 'medain',
            'email' => 'hzaeter01@gmail.com',
            'password' => Hash::make('password123'),
            'phone_number' => '0935917557'
        ]);


        $manager->assignRole('manager');
    }
}
