<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SensorController;
use App\Http\Controllers\ActuatorController;
use App\Http\Controllers\ScanningController;
use Illuminate\Support\Facades\Route;

// Halaman login
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Halaman yang butuh login
Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/sensors', [SensorController::class, 'sensorIndex'])->name('sensors');
    Route::post('/sensors', [SensorController::class, 'sensorStore'])->name('sensors.store');
    Route::delete('/sensors/{id}', [SensorController::class, 'sensorDestroy'])->name('sensors.destroy');
    Route::post('/actuator/command', [ActuatorController::class, 'sendCommand'])->name('actuator.command');
    Route::get('/scanning', [ScanningController::class, 'index'])->name('scanning');
    Route::get('/scanning/{id}/live', [ScanningController::class, 'liveView'])->name('scanning.live');
});