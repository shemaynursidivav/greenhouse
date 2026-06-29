<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SensorReading extends Model
{
    protected $fillable = [
        'device_id',
        'sensor_type',
        'value',
        'unit',
        'status',
    ];
}