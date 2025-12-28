<?php

use App\Http\Controllers\Driver\DashboardController;
use App\Http\Controllers\ProfileController;
use GuzzleHttp\Middleware;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Http\Controllers\User\DispatchController;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware(['auth', 'verified'])->group(function () {

    //管理者専用ルート
    Route::prefix('admin')->middleware('role:admin')->group(function (){
      Route::get('/dashboard', function () {
          return Inertia::render('Admin/AdminDashboard', [
              'role' => Auth::user()->role
            ]);
        })->name('admin.dashboard');
    });

    //ドライバー専用ルート
    Route::prefix('driver')->name('driver.')->group(function () {
      Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    });

    Route::post('/dispatches/{dispatch}/accept', [DashboardController::class, 'accept'])->name('dipatches.accept');

    //一般利用者専用ルート
   Route::prefix('user')->name('user.')->group(function () {
        // ★ 修正: ここにあったインポート文は削除します ★
        
        // 配車リクエストフォーム表示
        Route::get('/dispatch/form', [DispatchController::class, 'form'])->name('dispatch.form');
        
        // 配車リクエスト保存処理
        Route::post('/dispatch/store', [DispatchController::class, 'store'])->name('dispatch.store');
            
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
