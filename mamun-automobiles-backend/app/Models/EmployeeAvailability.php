<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\LogsActivity;

class EmployeeAvailability extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'employee_availabilities';

    protected $fillable = [
        'employee_id',
        'date',
        'status',
        'is_available',
        'notes',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'is_available' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
