<?php

namespace App\Http\Controllers;

use App\Models\Sensor;
use App\Models\SensorReading;

class DashboardController extends Controller
{
    public function index()
    {
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
}