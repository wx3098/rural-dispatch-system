<?php

use App\Http\Controllers\Driver\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Http\Controllers\User\DispatchController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware(['auth', 'verified'])->group(function () {

    // 管理者専用ルート
    Route::prefix('admin')->middleware('role:admin')->group(function (){
        Route::get('/dashboard', function () {
            return Inertia::render('Admin/AdminDashboard', [
                'role' => Auth::user()->role
            ]);
        })->name('admin.dashboard');
    });

    // ドライバー専用ルート
    Route::prefix('driver')->name('driver.')->middleware('role:driver')->group(function () {
        // ダッシュボード表示
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        
        // 配車受諾処理
        Route::post('/dispatches/{dispatch}/accept', [DashboardController::class, 'accept'])->name('dispatches.accept');
        
        // 配車完了処理
        Route::post('/dispatches/{dispatch}/complete', [DashboardController::class, 'complete'])->name('dispatches.complete');
    });

    // 一般利用者専用ルート
    Route::prefix('user')->name('user.')->group(function () {
        // 配車リクエストフォーム表示
        Route::get('/dispatch/form', [DispatchController::class, 'form'])->name('dispatch.form');
        // 配車リクエスト保存処理
        Route::post('/dispatch/store', [DispatchController::class, 'store'])->name('dispatch.store');
    });

    // プロフィール関連
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';