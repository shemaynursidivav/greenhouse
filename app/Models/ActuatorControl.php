<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActuatorControl extends Model
{
    protected $fillable = [
        'device_id',
        'actuator',
        'command',
        'value',
        'status',
    ];
}