<?php

namespace App\Http\Controllers;

use App\Models\Sensor;
use App\Models\SensorReading;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SensorController extends Controller
{
    /** Ambil setting dari tabel app_settings. */
    private function setting(string $name): ?string
    {
        $r = DB::table('app_settings')->where('name', $name)->first();
        return $r->value ?? null;
    }

    /** Klasifikasi status: Normal / Warning (deviasi <= 15%) / Danger (> 15%). */
    private function classify($pv, $min, $max): string
    {
        if ($min === null || $max === null) return 'normal';
        if ($pv >= $min && $pv <= $max)      return 'normal';

        $dev = $pv < $min
            ? ($min != 0 ? abs($pv - $min) / abs($min) : 1)
            : ($max != 0 ? abs($pv - $max) / abs($max) : 1);

        return $dev <= 0.15 ? 'warning' : 'danger';
    }

    /**
     * Endpoint penerima data dari node sensor (ESP32).
     * POST /api/sensor-data
     * Header: X-API-Key: <SENSOR_API_KEY>
     * Body  : {"device_id":"esp32_master","sensor_type":"temperature","value":28.6,"unit":"C"}
     */
    public function store(Request $request)
    {
        // --- Autentikasi sederhana ---
        $expected = config('app.sensor_api_key') ?: env('SENSOR_API_KEY');
        if ($expected && $request->header('X-API-Key') !== $expected) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'device_id'   => 'required|string',
            'sensor_type' => 'required|string',
            'value'       => 'required|numeric',
            'unit'        => 'required|string',
        ]);

        $sensor = Sensor::where('sensor_type', $validated['sensor_type'])
            ->where('device_id', $validated['device_id'])
            ->where('is_active', 1)
            ->first();

        $status = $sensor
            ? $this->classify($validated['value'], $sensor->threshold_min, $sensor->threshold_max)
            : 'normal';

        // Status sebelumnya (untuk anti-spam notifikasi)
        $prev = SensorReading::where('sensor_type', $validated['sensor_type'])
            ->where('device_id', $validated['device_id'])
            ->orderByDesc('id')
            ->value('status');

        $reading = SensorReading::create([
            'device_id'   => $validated['device_id'],
            'sensor_type' => $validated['sensor_type'],
            'value'       => $validated['value'],
            'unit'        => $validated['unit'],
            'status'      => $status,
        ]);

        // Kirim email hanya saat status BERUBAH ke warning/danger
        if ($sensor && in_array($status, ['warning', 'danger']) && $status !== $prev) {
            $this->sendAlert($sensor, $validated['value'], $status);
        }

        return response()->json([
            'message' => 'Data saved',
            'status'  => $status,
            'id'      => $reading->id,
        ], 201);
    }

    private function sendAlert($sensor, $value, $status): void
    {
        if ($this->setting('notify_enabled') !== '1') return;
        $to = $this->setting('notify_email');
        if (! $to) return;

        try {
            $lvl  = strtoupper($status);
            $body = "PERINGATAN STATUS SENSOR\n\n"
                  . "Sensor   : {$sensor->label} ({$sensor->sensor_type})\n"
                  . "Nilai PV : {$value} {$sensor->unit}\n"
                  . "Setpoint : {$sensor->threshold_min} - {$sensor->threshold_max} {$sensor->unit}\n"
                  . "Status   : {$lvl}\n"
                  . "Waktu    : " . now()->timezone('Asia/Jakarta')->format('d/m/Y H:i:s') . " WIB\n\n"
                  . "-- Greenhouse Monitor";

            Mail::raw($body, function ($m) use ($to, $lvl, $sensor) {
                $m->to($to)->subject("[{$lvl}] {$sensor->label} - Greenhouse Monitor");
            });
        } catch (\Throwable $e) {
            Log::warning('notify mail gagal: ' . $e->getMessage());
        }
    }

    public function latest()
    {
        return response()->json(
            SensorReading::orderBy('created_at', 'desc')->take(50)->get()
        );
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