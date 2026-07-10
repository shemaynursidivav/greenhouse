<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IrrigationLog extends Model
{
    protected $fillable = [
        'device_id',
        'soil_zona_1',
        'soil_zona_2',
        'soil_zona_3',
        'tinggi_tanaman',
        'fase_tanaman',
        'kondisi_kelembapan',
        'durasi_irigasi',
        'solenoid_status',
    ];
}