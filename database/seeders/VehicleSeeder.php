<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use App\Models\Vehicle;
use App\Models\User;

class VehicleSeeder extends Seeder
{
    public function run(): void
    {
            $driverToyota = User::where('email', 'driver1@rural.com')->first();
            $driverHonda = User::where('email', 'driver2@rural.com')->first();
     
        Vehicle::create([
            'license_plate' => '豊田01 と 10-01',
            'model_name' => 'Toyota ハイエース',
            'driver_id' => $driverToyota->id ?? null,
            'status' => 'available' //利用可能
        ]);

           Vehicle::create([
            'license_plate' => '品川02 み 20-02',
            'model_name' => 'Honda ステップワゴン',
            'driver_id' => $driverHonda->id ?? null,
            'status' => 'available',
        ]);

           Vehicle::create([
            'license_plate' => '品川03 み 30-03',
            'model_name' => 'Honda Fit',
            'driver_id' => null,
            'status' => 'maintenance', //メンテナンス
        ]);
    } 
}
