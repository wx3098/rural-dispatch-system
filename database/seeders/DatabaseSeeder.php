<?php

namespace Database\Seeders;

use App\Models\Dispatch;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use illuminate\support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        // 外部キーチェックを一時的にオフ（安全のため）
        Schema::disableForeignKeyConstraints();

        // 依存関係のある順序で呼び出す（この順序が重要！）
        $this->call([
            UserSeeder::class,
            DestinationSeeder::class,
            VehicleSeeder::class,
            DispatchSeeder::class,
        ]);

        // 外部キーチェックをオンに戻す
        Schema::enableForeignKeyConstraints();
    }
}
