<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class License extends Model
{
    use HasFactory;

    protected $fillable = [
        'license_key',
        'device_id',
        'plan',
        'status',
        'start_date',
        'expires_at',
        'last_opened_at',
        'features',
    ];

    protected $casts = [
        'start_date' => 'date',
        'expires_at' => 'date',
        'last_opened_at' => 'datetime',
        'features' => 'array',
    ];
}
