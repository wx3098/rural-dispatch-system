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
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            //どの車が配車したか
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->onDelete('set null');

            //目的地
            $table->foreignId('destination_id')->constrained('destinations')->onDelete('restrict');

            //状態遷移
            $table->enum('status', ['requested', 'allocated', 'in_progress', 'completed', 'cancelled'])->default('requested');

            //利用者希望日時
            $table->datetime('requested_pickup_datetime');

            //システム計算予定時刻
            $table->datetime('schedule_arrival_datetime')->nullable();

            //実際の送迎完了時刻
            $table->datetime('actual_completed_at')->nullable();

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
