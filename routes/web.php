<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SensorController;
use App\Http\Controllers\ActuatorController;
use App\Http\Controllers\ScanningController;
use App\Http\Controllers\IrrigationController;
use App\Http\Controllers\GantryController;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
    Route::get('/scanning', fn () => redirect()->route('gantry.index'))->name('scanning');
    Route::get('/scanning/{id}/live', [ScanningController::class, 'liveView'])->name('scanning.live');
    Route::get('/irrigation/latest', [IrrigationController::class, 'latest'])->name('irrigation.latest');

    // ── Gantry (RPi Dafa) ──
    Route::get('/gantry', [GantryController::class, 'index'])->name('gantry.index');
    Route::get('/gantry/sensors', [GantryController::class, 'sensors'])->name('gantry.sensors');
    Route::post('/gantry/start', [GantryController::class, 'start'])->name('gantry.start');
    Route::post('/gantry/{id}/stop', [GantryController::class, 'stop'])->name('gantry.stop');
    Route::get('/gantry/{id}/watch', [GantryController::class, 'watch'])->name('gantry.watch');
    Route::get('/gantry/live',      [App\Http\Controllers\GantryController::class, 'live'])->name('gantry.live');
    Route::get('/gantry/live/data', [App\Http\Controllers\GantryController::class, 'liveData'])->name('gantry.live.data');
    Route::get('/gantry/img',       [App\Http\Controllers\GantryController::class, 'image'])->name('gantry.img');
    Route::get('/gantry/recap', [App\Http\Controllers\GantryController::class, 'recap'])->name('gantry.recap');
    Route::get('/soil',         [App\Http\Controllers\SoilController::class, 'index'])->name('soil.index');
    Route::post('/soil/url',    [App\Http\Controllers\SoilController::class, 'saveUrl'])->name('soil.url');
    Route::get('/soil/poll',    [App\Http\Controllers\SoilController::class, 'poll'])->name('soil.poll');
    Route::get('/soil/history', [App\Http\Controllers\SoilController::class, 'history'])->name('soil.history');
    Route::get('/gantry/sessions', [App\Http\Controllers\GantryController::class, 'sessionsJson'])->name('gantry.sessions');
    Route::post('/notify/settings', [App\Http\Controllers\NotificationController::class, 'save'])->name('notify.save');
    Route::post('/notify/test',     [App\Http\Controllers\NotificationController::class, 'test'])->name('notify.test');
    });
