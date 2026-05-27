<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class administradores extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $user = Admin::create([
            'name' => 'Test User',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password')
        ]);

        User::create([
            'name' => 'Test User',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password')
        ]);

        Role::create([
            'name' => 'super_admin',
            'guard_name' => 'admin'
        ]);
        
        $user->assignRole('super_admin');
        $user->update();
    }
}
