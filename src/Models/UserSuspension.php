<?php

namespace Marufsharia\Hyro\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Config;
use Marufsharia\Hyro\Support\Traits\HasUuid;

class UserSuspension extends Model
{
    use HasUuid;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'reason',
        'details',
        'suspended_until',
        'suspended_by',
        'unsuspended_by',
        'unsuspended_at',
        'ip_address',
        'user_agent',
        'is_automatic',
        'auto_reason_code',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'suspended_at' => 'datetime',
        'suspended_until' => 'datetime',
        'unsuspended_at' => 'datetime',
        'is_automatic' => 'boolean',
    ];

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = true;

    /**
     * Get the table associated with the model.
     */
    public function getTable(): string
    {
        return Config::get('hyro.database.tables.user_suspensions', parent::getTable());
    }

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::creating(function (UserSuspension $suspension) {
            if (empty($suspension->suspended_at)) {
                $suspension->suspended_at = now();
            }
        });

        static::created(function (UserSuspension $suspension) {
            // Revoke user tokens if configured
            if (Config::get('hyro.security.suspension.auto_revoke_tokens', true)) {
                $suspension->user->tokens()->delete();
            }

            // Invalidate user cache
            app(CacheInvalidatorContract::class)->invalidateUserCache($suspension->user_id);
        });

        static::updated(function (UserSuspension $suspension) {
            if ($suspension->isDirty('unsuspended_at') && $suspension->unsuspended_at) {
                // User was unsuspended, invalidate cache
                app(CacheInvalidatorContract::class)->invalidateUserCache($suspension->user_id);
            }
        });
    }

    /**
     * Get the user who was suspended.
     */
    public function user(): BelongsTo
    {
        $userModel = Config::get('hyro.models.user');
        return $this->belongsTo($userModel, 'user_id');
    }

    /**
     * Get the admin who suspended the user.
     */
    public function suspender(): BelongsTo
    {
        $userModel = Config::get('hyro.models.user');
        return $this->belongsTo($userModel, 'suspended_by');
    }

    /**
     * Get the admin who unsuspended the user.
     */
    public function unsuspender(): BelongsTo
    {
        $userModel = Config::get('hyro.models.user');
        return $this->belongsTo($userModel, 'unsuspended_by');
    }

    /**
     * Check if suspension is currently active.
     */
    public function isActive(): bool
    {
        if ($this->unsuspended_at) {
            return false;
        }

        if ($this->suspended_until) {
            return now()->lessThan($this->suspended_until);
        }

        return true; // Indefinite suspension
    }

    /**
     * Check if suspension has expired.
     */
    public function isExpired(): bool
    {
        if ($this->unsuspended_at) {
            return true;
        }

        if ($this->suspended_until) {
            return now()->greaterThanOrEqualTo($this->suspended_until);
        }

        return false; // Indefinite never expires
    }

    /**
     * Scope: Get only active suspensions.
     */
    public function scopeActive($query)
    {
        return $query->whereNull('unsuspended_at')
            ->where(function ($q) {
                $q->whereNull('suspended_until')
                    ->orWhere('suspended_until', '>', now());
            });
    }

    /**
     * Scope: Get only expired suspensions.
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('unsuspended_at')
            ->orWhere(function ($q) {
                $q->whereNotNull('suspended_until')
                    ->where('suspended_until', '<=', now());
            });
    }

    /**
     * Scope: Get suspensions for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Unsuspend this suspension.
     */
    public function unsuspend(?int $byUserId = null): void
    {
        $this->unsuspended_at = now();
        $this->unsuspended_by = $byUserId;
        $this->save();
    }
}
