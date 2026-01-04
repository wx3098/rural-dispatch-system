<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('dispatches', function (Blueprint $table) {
            //1.位置情報（緯度・経度）を追加
            //目的地（到着時）の座標
            $table->decimal('end_lat', 10, 8)->nullable()->after('end_location');
            $table->decimal('end_lng', 11, 8)->nullable()->after('end_lat');

            //出発地の座標（お迎え時に近づいたことを検知したい場合に備えて）
            $table->decimal('start_lat', 10, 8)->nullable()->after('start_location');
            $table->decimal('start_lng', 11, 8)->nullable()->after('start_lat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dispatches', function (Blueprint $table) {
            $table->dropColumn(['start_lng', 'start_lat', 'end_lng', 'end_lat']);
        });
    }
};
