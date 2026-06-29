<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScanningSession extends Model
{
    protected $fillable = [
        'jumlah_tanaman',
        'jarak_antar_tanaman',
        'jarak_frame_ke_tanaman',
        'susunan_tanaman',
        'baris',
        'kolom',
        'servo_pan',
        'servo_tilt',
        'stream_url',
        'penyiraman',
        'status',
        'progress',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'penyiraman' => 'boolean',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function results()
    {
        return $this->hasMany(ScanningResult::class, 'session_id');
    }
}