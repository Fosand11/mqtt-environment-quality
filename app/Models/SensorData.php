<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class SensorData extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'sensor_data';

    protected $fillable = [
        'temperature',
        'humidity',
        'air_quality',
        'timestamp',
        'alert',
    ];

    protected $casts = [
        'temperature' => 'float',
        'humidity' => 'float',
        'air_quality' => 'float',
        'timestamp' => 'datetime',
        'alert' => 'array',
    ];
}
