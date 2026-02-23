<?php

namespace Marufsharia\Hyro\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the activity log
     */
    public function user()
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }
    
    /**
     * Scope for specific action
     */
    public function scopeForAction($query, string $action)
    {
        return $query->where('action', $action);
    }
    
    /**
     * Scope for date range
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }
}

