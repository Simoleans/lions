<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\{
    AttendanceController,
    ProfileController,
    UsersController
};

Route::get('/', function () {
    dd("ssss");
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    //users resources
    Route::resource('users', UsersController::class);
    Route::post('/users/{user}/renew-subscription', [UsersController::class, 'renewSubscription'])->name('users.renew-subscription');

    Route::resource('attendances', AttendanceController::class);
});

//attendance create
Route::get('/asistencias/escanear', [AttendanceController::class, 'create'])->name('attendance.create');
Route::post('/asistencias/escanear', [AttendanceController::class, 'store'])->name('attendance.store');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
