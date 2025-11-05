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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['user', 'driver', 'admin'])->default('user')->after('password');

            $table->float('current_lat', 10, 6)->nullable();
            $table->float('current_lng', 10, 6)->nullable();

            $table->string('phone_number', 30)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'current_lat', 'current_lng', 'phone_number']);

        });
    }
};
