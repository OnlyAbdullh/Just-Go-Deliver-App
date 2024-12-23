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


        foreach ($roles as $roleName) {
             Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'api']);
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
