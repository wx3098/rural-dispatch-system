<?php

use App\Http\Controllers\ProfileController;
use GuzzleHttp\Middleware;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

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
    Route::prefix('admin')->middleware('role.admin')->group(function (){
        Route::get('/dashboard', function () {
            return Inertia::render('Admin/AdminDashboard', [
                'role' => Auth::user()->role
            ]);
        })->name('admin.dashboard');
    });

    //ドライバー専用ルート
    Route::prefix('driver')->middleware('role:driver')->group(function () {
        Route::get('/dashboard', function () {
            return Inertia::render('Driver/DriverDashboard', [
                'role' => Auth::user()->role
            ]);
        })->name('driver.dashboard');
    });

    //一般利用者専用ルート
    Route::prefix('user')->middleware('role:user')->group(function () {
        Route::get('/dispatch', function () {
            return Inertia::render('User/DispatchRequestForm', [
                'role' => Auth::user()->role
            ]);
        })->name('user.dispatch.form');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
