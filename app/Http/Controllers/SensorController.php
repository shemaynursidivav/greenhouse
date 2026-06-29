<?php

namespace App\Http\Controllers;

use App\Events\SensorAlert;
use App\Models\Sensor;
use App\Models\SensorReading;
use Illuminate\Http\Request;

class SensorController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'device_id'   => 'required|string',
            'sensor_type' => 'required|string',
            'value'       => 'required|numeric',
            'unit'        => 'required|string',
        ]);

        // Cari konfigurasi sensor
        $sensor = Sensor::where('sensor_type', $validated['sensor_type'])
            ->where('device_id', $validated['device_id'])
            ->where('is_active', 1)
            ->first();

        // Tentukan status
        $status = 'normal';
        if ($sensor && $sensor->threshold_min !== null && $sensor->threshold_max !== null) {
            $pv  = $validated['value'];
            $min = $sensor->threshold_min;
            $max = $sensor->threshold_max;

            if ($pv < $min || $pv > $max) {
                $deltaMin = abs($pv - $min) / ($min != 0 ? $min : 1);
                $deltaMax = abs($pv - $max) / ($max != 0 ? $max : 1);
                $delta    = min($deltaMin, $deltaMax);
                $status   = $delta > 0.20 ? 'danger' : 'warning';
            }
        }

        // Simpan ke database
        $reading = SensorReading::create([
            'device_id'   => $validated['device_id'],
            'sensor_type' => $validated['sensor_type'],
            'value'       => $validated['value'],
            'unit'        => $validated['unit'],
            'status'      => $status,
        ]);

        // Broadcast notifikasi kalau tidak normal
        if ($status !== 'normal') {
            broadcast(new SensorAlert(
                $validated['device_id'],
                $validated['sensor_type'],
                $validated['value'],
                $validated['unit'],
                $status
            ));
        }

        return response()->json([
            'message' => 'Data saved',
            'status'  => $status,
            'id'      => $reading->id,
        ], 201);
    }

    public function latest()
    {
        $readings = SensorReading::orderBy('created_at', 'desc')
            ->take(50)
            ->get();

        return response()->json($readings);
    }

    public function sensorIndex()
    {
        $sensors = Sensor::orderBy('created_at', 'desc')->get();
        return view('sensors', compact('sensors'));
    }

    public function sensorStore(Request $request)
    {
        $request->validate([
            'device_id'     => 'required|string',
            'sensor_type'   => 'required|string',
            'label'         => 'required|string',
            'unit'          => 'required|string',
            'owner'         => 'required|string',
            'threshold_min' => 'nullable|numeric',
            'threshold_max' => 'nullable|numeric',
        ]);

        Sensor::create($request->all());
        return redirect('/sensors')->with('success', 'Sensor berhasil ditambahkan!');
    }

    public function sensorDestroy(int $id)
    {
        Sensor::findOrFail($id)->delete();
        return redirect('/sensors')->with('success', 'Sensor berhasil dihapus!');
    }
}