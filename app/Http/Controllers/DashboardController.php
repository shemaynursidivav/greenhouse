<?php

namespace App\Http\Controllers;

use App\Models\Sensor;
use App\Models\SensorReading;
use App\Services\SensorSync;

class DashboardController extends Controller
{
    public function __construct(private SensorSync $sync) {}

    public function index()
    {
        // Tarik data terbaru dari Rio + Dafa lalu tulis ke sensor_readings.
        // Dibungkus try/catch: kalau sumber offline, halaman tetap tampil (data terakhir).
        try { $this->sync->run(); } catch (\Throwable $e) {}

        $sensors = Sensor::where('is_active', 1)->get();

        $latestReadings = [];
        foreach ($sensors as $sensor) {
            $reading = SensorReading::where('sensor_type', $sensor->sensor_type)
                ->where('device_id', $sensor->device_id)
                ->orderBy('created_at', 'desc')
                ->first();
            if ($reading) {
                $latestReadings[$sensor->sensor_type] = $reading;
            }
        }

        $logs = SensorReading::orderBy('created_at', 'desc')->take(50)->get();

        return view('dashboard', compact('sensors', 'latestReadings', 'logs'));
    }

    /** Endpoint manual untuk tombol "Perbarui" (opsional, kembalikan JSON). */
    public function sync()
    {
        try {
            $r = $this->sync->run();
            return response()->json(['ok' => true] + $r);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }
}