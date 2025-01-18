<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

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
            'password' => Hash::make('admin12345'),
            'phone_number' => '0935917557',
        ]);

        $manager->assignRole('manager');
    }
}
