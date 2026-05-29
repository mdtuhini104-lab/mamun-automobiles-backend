<?php

namespace App\Models;

use App\Traits\MultitenantSafe;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResolutionWorkflow extends Model
{
    use HasFactory, MultitenantSafe;

    protected $fillable = [
        'tenant_id',
        'incident_id',
        'steps',
        'solution',
        'kb_article_id'
    ];

    protected $casts = [
        'steps' => 'array',
    ];

    public function incident(): BelongsTo
    {
        return $this->belongsTo(SupportIncident::class, 'incident_id');
    }

    public function kbArticle(): BelongsTo
    {
        return $this->belongsTo(KnowledgeBaseArticle::class, 'kb_article_id');
    }
}
