<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sensor extends Model
{
    protected $fillable = [
        'device_id',
        'sensor_type',
        'label',
        'unit',
        'owner',
        'threshold_min',
        'threshold_max',
        'is_active',
    ];
}