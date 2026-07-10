<?php

namespace App\Http\Controllers;

use App\Models\IrrigationLog;
use Illuminate\Http\Request;

class IrrigationController extends Controller
{
    // ESP32 kirim hasil fuzzy ke sini
    public function store(Request $request)
    {
        $validated = $request->validate([
            'device_id'          => 'required|string',
            'soil_zona_1'        => 'nullable|numeric',
            'soil_zona_2'        => 'nullable|numeric',
            'soil_zona_3'        => 'nullable|numeric',
            'tinggi_tanaman'     => 'nullable|numeric',
            'fase_tanaman'       => 'nullable|string',
            'kondisi_kelembapan' => 'nullable|string',
            'durasi_irigasi'     => 'required|integer',
            'solenoid_status'    => 'required|string',
        ]);

        $log = IrrigationLog::create($validated);

        return response()->json([
            'message' => 'Irrigation data saved',
            'id'      => $log->id,
        ], 201);
    }

    // Dashboard ambil data irigasi terbaru
    public function latest()
    {
        $log = IrrigationLog::orderBy('created_at', 'desc')->first();

        return response()->json($log);
    }
}