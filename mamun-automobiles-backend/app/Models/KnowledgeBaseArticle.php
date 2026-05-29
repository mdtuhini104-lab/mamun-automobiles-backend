<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KnowledgeBaseArticle extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'title',
        'slug',
        'category',
        'content',
        'is_published'
    ];

    protected $casts = [
        'is_published' => 'boolean'
    ];

    /**
     * Boot scope for the KnowledgeBaseArticle model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function (Model $model) {
            // Keep tenant_id null for global if not explicitly set
        });

        static::addGlobalScope('tenant_or_global', function (Builder $builder) {
            if (auth()->check()) {
                $user = auth()->user();
                if ($user->hasRole('Super Admin')) {
                    return;
                }
                $builder->where(function ($q) use ($user) {
                    $q->whereNull($q->getModel()->getTable() . '.tenant_id')
                      ->orWhere($q->getModel()->getTable() . '.tenant_id', $user->tenant_id);
                });
            } else {
                // If guest, show only global articles
                $builder->whereNull('tenant_id');
            }
        });
    }
}
