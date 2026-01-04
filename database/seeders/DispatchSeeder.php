<?php

namespace Database\Seeders;

use App\Models\Dispatch;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Destination;
use Illuminate\Database\Seeder;

class DispatchSeeder extends Seeder
{
    public function run(): void
    {
        //必要なデータを取得
        $clientUser = User::where('email', 'user@rural.com')->first();
        $vehicle1 = Vehicle::where('license_plate', '豊田01 と 10-01')->first();
        $hospital = Destination::where('name', '中央総合病院')->first();
    

    // 配車依頼を作成
    if ($clientUser && $vehicle1 && $hospital) {
        Dispatch::create([
            'user_id' => $clientUser->id,
            'start_location' => '福岡県北九州市小倉南区平尾台 1丁目5-3',
            'start_lat' => 33.7667,
            'start_lng' => 130.9000,
            'end_location' => '福岡県北九州市戸畑区新池 15番地', 
            'end_lat' => 33.8917,
            'end_lng' => 130.8250,
            'vehicle_id' => $vehicle1->id,
            'destination_id' =>$hospital->id,
            'status' => 'pending',
            'driver_id' => null,
            'requested_pickup_datetime' => now()->addMinutes(30),
        ]);

        // テスト用に、もう一人「同じ目的地」への依頼を作成（相乗りのテスト用）
            Dispatch::create([
                'user_id' => $clientUser->id, // 本来は別のユーザーが望ましい
                'start_location' => '福岡県北九州市小倉南区某所',
                'start_lat' => 33.7700,
                'start_lng' => 130.9100,

                'end_location' => '福岡県北九州市戸畑区新池 15番地', 
                'end_lat' => 33.8917,
                'end_lng' => 130.8250,

                'vehicle_id' => $vehicle1->id,
                'destination_id' => $hospital->id,
                'status' => 'pending',
                'driver_id' => null,
                'requested_pickup_datetime' => now()->addMinutes(45),
            ]);
    }
  }
}
