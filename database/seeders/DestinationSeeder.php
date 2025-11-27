<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Destination;

class DestinationSeeder extends Seeder
{
    public function run(): void
    {
       
        Destination::create([
            'name' => '中央総合病院',
            'address' => '福岡県福岡市中央区...',
            'latitude' => 33.5900,
            'longitude' => 130.4000,
            'category' => 'hospital',
        ]);

        Destination::create([
            'name' => '地域クリニック',
            'address' => '福岡県福岡市東区...',
            'latitude' => 33.6050,
            'longitude' => 130.4300,
            'category' => 'clinic',
        ]);
        
        Destination::create([
            'name' => '市役所',
            'address' => '福岡県福岡市博多区...',
            'latitude' => 33.5840,
            'longitude' => 130.4180,
            'category' => 'public_facility',
        ]);
    }
}

