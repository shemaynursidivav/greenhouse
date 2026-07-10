<?php

use App\Http\Controllers\SensorController;
use App\Http\Controllers\ActuatorController;
use App\Http\Controllers\ScanningController;
use App\Http\Controllers\IrrigationController;
use Illuminate\Support\Facades\Route;

Route::post('/sensor-data', [SensorController::class, 'store']);
Route::get('/sensor-latest', [SensorController::class, 'latest']);
Route::get('/actuator/command', [ActuatorController::class, 'getCommand']);
Route::post('/actuator/confirm/{id}', [ActuatorController::class, 'confirmCommand']);

Route::post('/scanning/session', [ScanningController::class, 'createSession']);
Route::post('/scanning/penyiraman', [ScanningController::class, 'createPenyiraman']);
Route::get('/scanning/session/active', [ScanningController::class, 'getActiveSession']);
Route::put('/scanning/session/{id}', [ScanningController::class, 'updateSession']);
Route::post('/scanning/session/{id}/stream', [ScanningController::class, 'updateStreamUrl']);
Route::post('/scanning/session/{id}/result', [ScanningController::class, 'submitResult']);
Route::get('/scanning/session/{id}/results', [ScanningController::class, 'getResults']);
Route::post('/scanning/session/{id}/status',   [ScanningController::class, 'updateStatus']);
Route::post('/scanning/session/{id}/progress', [ScanningController::class, 'updateProgress']);
Route::get('/scanning/rekap',                  [ScanningController::class, 'getRekap']);
// ── Irigasi (hasil fuzzy dari ESP32) ──
Route::post('/irrigation-data', [IrrigationController::class, 'store']);