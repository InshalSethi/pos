<?php

namespace App\Traits;

use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Model;

trait LogsActivity
{
    /**
     * Boot the trait to add Eloquent model listeners.
     */
    public static function bootLogsActivity(): void
    {
        static::created(function (Model $model) {
            if (static::shouldLogActivity('created')) {
                $title = ActivityLogger::resolveSubjectTitle($model);
                $type = static::getActivityLogType();
                $modelName = class_basename($model);

                ActivityLogger::log(
                    type: $type,
                    event: 'created',
                    description: "Created new {$modelName}: {$title}",
                    subject: $model,
                    subjectTitle: $title,
                    properties: [
                        'attributes' => $model->getAttributes()
                    ]
                );
            }
        });

        static::updated(function (Model $model) {
            if (static::shouldLogActivity('updated')) {
                $changes = $model->getChanges();
                // Exclude timestamps unless explicitly requested
                unset($changes['updated_at']);

                if (!empty($changes)) {
                    $original = array_intersect_key($model->getOriginal(), $changes);
                    $title = ActivityLogger::resolveSubjectTitle($model);
                    $type = static::getActivityLogType();
                    $modelName = class_basename($model);

                    ActivityLogger::log(
                        type: $type,
                        event: 'updated',
                        description: "Updated {$modelName}: {$title}",
                        subject: $model,
                        subjectTitle: $title,
                        properties: [
                            'old' => $original,
                            'new' => $changes,
                        ]
                    );
                }
            }
        });

        static::deleted(function (Model $model) {
            if (static::shouldLogActivity('deleted')) {
                $title = ActivityLogger::resolveSubjectTitle($model);
                $type = static::getActivityLogType();
                $modelName = class_basename($model);

                ActivityLogger::log(
                    type: $type,
                    event: 'deleted',
                    description: "Deleted {$modelName}: {$title}",
                    subject: $model,
                    subjectTitle: $title,
                    properties: [
                        'attributes' => $model->getAttributes()
                    ]
                );
            }
        });
    }

    /**
     * Get the log type for this model (defaults to 'crud').
     */
    protected static function getActivityLogType(): string
    {
        return property_exists(static::class, 'activityLogType') 
            ? static::$activityLogType 
            : 'crud';
    }

    /**
     * Determine if an event should be logged.
     */
    protected static function shouldLogActivity(string $event): bool
    {
        return true;
    }
}
