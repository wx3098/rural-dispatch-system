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
        Schema::create('dispatches', function (Blueprint $table) {
            $table->id();

            //誰の依頼か？
            $table->foreignId('user_id')->constrained()->comment('依頼ユーザーID');

            $table->foreignId('driver_id')->nullable()->constrained('users')->comment('担当ドライバーID');

            $table->string('start_location')->comment('出発地（ユーザー入力）');
            
            $table->string('end_location')->comment('目的地（ユーザー入力）');

            $table->timestamp('requested_pickup_datetime')->comment('希望乗車日時');

            //状態遷移
             $table->enum('status', ['pending', 'assigned', 'completed', 'cancelled'])->default('pending')->comment('ステータス');

            //どの車が配車したか
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->comment('割り当て車両ID');

            //目的地
            $table->foreignId('destination_id')->nullable()->constrained('destinations')->comment('目的地マスタID');

            //システム計算予定時刻
            $table->datetime('schedule_arrival_datetime')->nullable()->comment('到着予定日時');

            //実際の送迎完了日時
            $table->timestamp('actual_completed_at')->nullable()->comment('完了日時');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dispatches');
    }
};
