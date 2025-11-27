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
        $clinentUser = User::where('email', 'user@rural.com')->first();
        $vehicle1 = Vehicle::where('license_plate', '豊田01 と 10-01')->first();
        $hospital = Destination::where('name', '中央総合病院')->first();
    

    // 配車依頼を作成
    if ($clinentUser && $vehicle1 && $hospital) {
        Dispatch::create([
            'user_id' => $clinentUser->id,
            'vehicle_id' => $vehicle1->id,
            'destination_id' =>$hospital->id,
            'status' => 'allocated',
            'requested_pickup_datetime' => now()->addHour(),
        ]);
    }
  }
}
