<?php

namespace App\Traits;

use App\Services\ActivityLogService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait LogsActivity
{
    public static function bootLogsActivity()
    {
        static::created(function (Model $model) {
            self::logAction($model, 'created');
        });

        static::updated(function (Model $model) {
            self::logAction($model, 'updated');
        });

        static::deleted(function (Model $model) {
            self::logAction($model, 'deleted');
        });
    }

    protected static function logAction(Model $model, string $action)
    {
        // Don't log if running from console/seeder unless forced
        if (app()->runningInConsole() && !config('app.log_console_activity')) {
            return;
        }

        $module = class_basename($model);
        $description = "{$module} was {$action} (ID: {$model->getKey()})";
        
        $oldValues = $action === 'updated' || $action === 'deleted' ? $model->getOriginal() : null;
        $newValues = $action === 'updated' || $action === 'created' ? $model->getAttributes() : null;
        
        if ($action === 'updated') {
            // Only log what actually changed
            $newValues = $model->getChanges();
            if (empty($newValues)) return;
            $oldValues = array_intersect_key($oldValues, $newValues);
        }

        // Hide hidden attributes (like passwords)
        if ($oldValues) {
            foreach ($model->getHidden() as $hidden) {
                unset($oldValues[$hidden]);
            }
        }
        if ($newValues) {
            foreach ($model->getHidden() as $hidden) {
                unset($newValues[$hidden]);
            }
        }

        ActivityLogService::log(
            module: $module,
            action: $action,
            description: $description,
            oldValues: $oldValues,
            newValues: $newValues,
            severity: $action === 'deleted' ? 'warning' : 'info'
        );
    }
}
