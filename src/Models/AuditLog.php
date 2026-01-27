<?php

namespace Marufsharia\Hyro\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Marufsharia\Hyro\Support\Traits\HasUuid;

class AuditLog extends Model
{
    use HasUuid;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'auditable_type',
        'auditable_id',
        'user_id',
        'event',
        'subject_type',
        'subject_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'url',
        'method',
        'tags',
        'description',
        'batch_uuid',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'tags' => 'array',
        'logged_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'auditable_id',
        'user_id',
        'subject_id',
    ];

    /**
     * Get the table associated with the model.
     */
    public function getTable(): string
    {
        return Config::get('hyro.database.tables.audit_logs', parent::getTable());
    }

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::creating(function (AuditLog $log) {
            // Set batch UUID if not provided
            if (empty($log->batch_uuid)) {
                $log->batch_uuid = session()->get('hyro_audit_batch_uuid') ?? (string) \Illuminate\Support\Str::uuid();
            }

            // Sanitize sensitive data
            $log->sanitizeSensitiveData();
        });
    }

    /**
     * Sanitize sensitive data before storing.
     */
    private function sanitizeSensitiveData(): void
    {
        $sensitiveFields = Config::get('hyro.auditing.sensitive_fields', [
            'password',
            'password_confirmation',
            'token',
            'api_key',
            'secret',
            'private_key',
        ]);

        // Sanitize old values
        if ($this->old_values) {
            foreach ($sensitiveFields as $field) {
                if (Arr::has($this->old_values, $field)) {
                    Arr::set($this->old_values, $field, '***REDACTED***');
                }
            }
        }

        // Sanitize new values
        if ($this->new_values) {
            foreach ($sensitiveFields as $field) {
                if (Arr::has($this->new_values, $field)) {
                    Arr::set($this->new_values, $field, '***REDACTED***');
                }
            }
        }
    }

    /**
     * Get the auditable model.
     */
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the users who performed the action.
     */
    public function user(): MorphTo
    {
        $userModel = Config::get('hyro.models.users');
        return $this->morphTo()->whereMorphType($userModel);
    }

    /**
     * Get the subject of the action.
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the changed attributes.
     */
    public function getChangedAttributes(): array
    {
        if (empty($this->old_values) || empty($this->new_values)) {
            return [];
        }

        $changed = [];
        $allKeys = array_unique(
            array_merge(
                array_keys($this->old_values),
                array_keys($this->new_values)
            )
        );

        foreach ($allKeys as $key) {
            $old = Arr::get($this->old_values, $key);
            $new = Arr::get($this->new_values, $key);

            if ($old !== $new) {
                $changed[$key] = [
                    'old' => $old,
                    'new' => $new,
                ];
            }
        }

        return $changed;
    }

    /**
     * Scope: Get logs for a specific auditable model.
     */
    public function scopeForAuditable(Builder $query, $type, $id): Builder
    {
        return $query->where('auditable_type', $type)
            ->where('auditable_id', $id);
    }

    /**
     * Scope: Get logs for a specific users.
     */
    public function scopeForUser(Builder $query, $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Get logs for a specific event.
     */
    public function scopeForEvent(Builder $query, string $event): Builder
    {
        return $query->where('event', $event);
    }

    /**
     * Scope: Get logs within a date range.
     */
    public function scopeBetweenDates(Builder $query, \DateTimeInterface $from, \DateTimeInterface $to): Builder
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    /**
     * Scope: Get logs with specific tags.
     */
    public function scopeWithTags(Builder $query, array $tags): Builder
    {
        return $query->whereJsonContains('tags', $tags);
    }

    /**
     * Scope: Get logs from a specific batch.
     */
    public function scopeInBatch(Builder $query, string $batchUuid): Builder
    {
        return $query->where('batch_uuid', $batchUuid);
    }

    /**
     * Clean up old audit logs based on retention policy.
     */
    public static function cleanupOldLogs(): int
    {
        $retentionDays = Config::get('hyro.auditing.retention_days', 365);
        $cutoffDate = now()->subDays($retentionDays);

        return self::where('created_at', '<', $cutoffDate)->delete();
    }

    /**
     * Log an audit event.
     */
    public static function log(string $event, $auditable = null, $subject = null, array $changes = [], array $metadata = []): self
    {
        // Check if auditing is enabled
        if (!Config::get('hyro.auditing.enabled', true)) {
            return new self(); // Return empty model
        }

        // Check if this event should be logged
        $enabledEvents = Config::get('hyro.auditing.events', []);
        if (!in_array($event, $enabledEvents) && !in_array('*', $enabledEvents)) {
            return new self();
        }

        $log = new self([
            'event' => $event,
            'old_values' => $changes['old'] ?? null,
            'new_values' => $changes['new'] ?? null,
            'description' => $metadata['description'] ?? null,
            'tags' => $metadata['tags'] ?? null,
            'ip_address' => $metadata['ip_address'] ?? request()->ip(),
            'user_agent' => $metadata['user_agent'] ?? request()->userAgent(),
            'url' => $metadata['url'] ?? request()->fullUrl(),
            'method' => $metadata['method'] ?? request()->method(),
        ]);

        // Set auditable
        if ($auditable) {
            $log->auditable_type = get_class($auditable);
            $log->auditable_id = $auditable->getKey();
        }

        // Set subject
        if ($subject) {
            $log->subject_type = get_class($subject);
            $log->subject_id = $subject->getKey();
        }

        // Set users
        $log->user_id = auth()->id();

        $log->save();

        return $log;
    }
}
