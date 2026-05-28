<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\LogsActivity;

class Designation extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'code',
        'department_id',
        'parent_designation_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function parentDesignation(): BelongsTo
    {
        return $this->belongsTo(Designation::class, 'parent_designation_id');
    }

    public function childDesignations(): HasMany
    {
        return $this->hasMany(Designation::class, 'parent_designation_id');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
