<?php

namespace Marufsharia\Hyro\Core\Traits;

use Illuminate\Support\Facades\Auth;
use Marufsharia\Hyro\Core\Models\AuditLog;

trait LogsAuditEvents
{
    /**
     * Log an audit event.
     */
    protected function logAuditEvent(
        string $eventName,
        $model,
        $actor = null,
        array $oldValues = [],
        array $newValues = [],
        array $metadata = []
    ): void {
        $data = [
            'event' => $eventName,
            'auditable_type' => is_object($model) ? get_class($model) : $model,
            'auditable_id' => is_object($model) ? $model->id : null,
            'user_id' => Auth::id() ?? $actor?->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ];

        if (!empty($oldValues)) {
            $data['old_values'] = $oldValues;
        }

        if (!empty($newValues) || !empty($metadata)) {
            $data['new_values'] = array_merge($newValues, [
                'actor_id' => $actor?->id,
                'actor_type' => $actor ? get_class($actor) : null,
                'via' => $metadata['via'] ?? 'manual',
                'reason' => $metadata['reason'] ?? null,
            ]);
        }

        AuditLog::create($data);
    }

    /**
     * Log a creation event.
     */
    protected function logCreated($model, $creator = null, array $metadata = []): void
    {
        $eventName = $this->getEventName($model, 'created');
        $this->logAuditEvent($eventName, $model, $creator, [], [], $metadata);
    }

    /**
     * Log an update event.
     */
    protected function logUpdated($model, array $original, $updater = null, array $metadata = []): void
    {
        $eventName = $this->getEventName($model, 'updated');
        $changes = is_object($model) ? $model->getChanges() : [];
        $this->logAuditEvent($eventName, $model, $updater, $original, $changes, $metadata);
    }

    /**
     * Log a deletion event.
     */
    protected function logDeleted($model, $deleter = null, array $metadata = []): void
    {
        $eventName = $this->getEventName($model, 'deleted');
        $this->logAuditEvent($eventName, $model, $deleter, [], [], $metadata);
    }

    /**
     * Get event name from model.
     */
    protected function getEventName($model, string $action): string
    {
        $className = is_object($model) ? class_basename($model) : class_basename($model);
        return strtolower($className) . '_' . $action;
    }
}

