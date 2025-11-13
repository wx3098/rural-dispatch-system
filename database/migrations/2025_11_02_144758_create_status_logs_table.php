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
        Schema::create('status_logs', function (Blueprint $table) {
            $table->id();

            //どの配車依頼の状態が変わったか
            //依頼が削除されたら、履歴も削除
            $table->foreignId('dispatch_id')->constrained('dispatches')->onDelete('cascade');

            //変更前の状態
            $table->string('status_before', 50);

            //変更後の状態
            $table->string('status_after', 50);

            //誰が変更操作を行ったか
            $table->foreignId('chenged_by_user_id')->nullable()->constrained('users')->onDelete('set null');

            //ログの記録日時
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('status_logs_');
    }
};
