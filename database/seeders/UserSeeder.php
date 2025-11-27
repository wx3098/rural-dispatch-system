<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
       
        User::create([
            'name' => 'Admin Manager',
            'email' => 'admin@rural.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // 一般利用者
        User::create([
            'name' => '山田 利用者',
            'email' => 'user@rural.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'current_lat' => 31.911096, 
            'current_lng' => 131.423855,
        ]);

        //ドライバーアカウント
        $driver1 = User::create([
            'name' => 'toyota',
            'email' => 'driver1@rural.com',
            'password' => Hash::make('password'),
            'role' => 'driver',
            'phone_number' => '08012345678',
            'current_lat' => 35.05826,
            'current_lng' => 137.15607,
        ]);

             $driver2 = User::create([
            'name' => 'honda',
            'email' => 'driver2@rural.com',
            'password' => Hash::make('password'),
            'role' => 'driver',
            'phone_number' => '0803334444',
            'current_lat' => 35.66972,
            'current_lng' => 139.74708,
        ]);
    }
}
