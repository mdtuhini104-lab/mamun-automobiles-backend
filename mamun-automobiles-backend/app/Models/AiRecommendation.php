<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiRecommendation extends Model
{
    use HasFactory;

    protected $fillable = [
        'recommendation_type',
        'source_id',
        'suggestion_data',
        'confidence_score',
        'explanation',
        'status',
        'outcome',
        'effectiveness_score',
        'actioned_by_id',
        'actioned_at',
    ];

    protected $casts = [
        'suggestion_data' => 'array',
        'confidence_score' => 'float',
        'effectiveness_score' => 'float',
        'actioned_at' => 'datetime',
    ];

    public function actionedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actioned_by_id');
    }
}
