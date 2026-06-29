<?php

namespace App\Http\Controllers;

use App\Models\ActuatorControl;
use Illuminate\Http\Request;

class ActuatorController extends Controller
{
    // Dashboard kirim perintah ke aktuator
    public function sendCommand(Request $request)
    {
        $request->validate([
            'device_id' => 'required|string',
            'actuator'  => 'required|string',
            'command'   => 'required|string',
            'value'     => 'nullable|string',
        ]);

        // Hapus perintah lama yang masih pending untuk aktuator ini
        ActuatorControl::where('device_id', $request->device_id)
            ->where('actuator', $request->actuator)
            ->where('status', 'pending')
            ->delete();

        // Simpan perintah baru
        $control = ActuatorControl::create([
            'device_id' => $request->device_id,
            'actuator'  => $request->actuator,
            'command'   => $request->command,
            'value'     => $request->value,
            'status'    => 'pending',
        ]);

        return response()->json([
            'message' => 'Command sent',
            'id'      => $control->id,
        ], 201);
    }

    // ESP32 ambil perintah terbaru
    public function getCommand(Request $request)
    {
        $deviceId = $request->query('device_id', 'esp32_master');

        $commands = ActuatorControl::where('device_id', $deviceId)
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        // Tandai sudah diambil
        ActuatorControl::where('device_id', $deviceId)
            ->where('status', 'pending')
            ->update(['status' => 'delivered']);

        return response()->json($commands);
    }

    // ESP32 konfirmasi perintah sudah dijalankan
    public function confirmCommand($id)
    {
        $control = ActuatorControl::findOrFail($id);
        $control->update(['status' => 'executed']);

        return response()->json(['message' => 'Command executed']);
    }
}