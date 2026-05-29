<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemHealthAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'alert_type',
        'severity',
        'message',
        'metrics',
        'resolved_at',
    ];

    protected $casts = [
        'metrics' => 'array',
        'resolved_at' => 'datetime',
    ];
}
