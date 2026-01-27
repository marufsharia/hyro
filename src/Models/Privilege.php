<?php

namespace Marufsharia\Hyro\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Carbon;

class Privilege extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'category',
        'description',
        'is_core',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_core' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the table name from config.
     */
    public function getTable(): string
    {
        return Config::get('hyro.database.tables.privileges', parent::getTable());
    }

    /**
     * Get roles that have this privilege.
     */
    public function roles(): BelongsToMany
    {
        $roleModel = Config::get('hyro.database.models.role');

        // Fallback to default if config is not set
        if (!$roleModel || !class_exists($roleModel)) {
            $roleModel = \Marufsharia\Hyro\Models\Role::class;
        }

        $pivotTable = Config::get('hyro.database.tables.privilege_role');

        if (!$pivotTable) {
            $pivotTable = 'hyro_privilege_role';
        }

        return $this->belongsToMany($roleModel, $pivotTable)
            ->withPivot(['granted_by', 'granted_at', 'grant_reason', 'conditions', 'expires_at'])
            ->withTimestamps()
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Get all users who have this privilege through their roles.
     */
    public function users(): BelongsToMany
    {
        $userModel = Config::get('hyro.database.models.user');

        // Fallback to default if config is not set
        if (!$userModel || !class_exists($userModel)) {
            $userModel = \App\Models\User::class;
        }

        $roleModel = Config::get('hyro.database.models.role');
        if (!$roleModel || !class_exists($roleModel)) {
            $roleModel = \Marufsharia\Hyro\Models\Role::class;
        }

        $userRoleTable = Config::get('hyro.database.tables.role_user');
        $privilegeRoleTable = Config::get('hyro.database.tables.privilege_role');

        if (!$userRoleTable) {
            $userRoleTable = 'hyro_role_user';
        }

        if (!$privilegeRoleTable) {
            $privilegeRoleTable = 'hyro_privilege_role';
        }

        return $this->belongsToMany($userModel, $privilegeRoleTable, 'privilege_id', 'role_id')
            ->join($userRoleTable, function ($join) use ($userRoleTable, $privilegeRoleTable) {
                $join->on("$userRoleTable.role_id", '=', "$privilegeRoleTable.role_id");
            })
            ->select("{$userModel}::table.*")
            ->distinct();
    }

    /**
     * Scope to get core privileges.
     */
    public function scopeCore($query)
    {
        return $query->where('is_core', true);
    }

    /**
     * Scope to get non-core privileges.
     */
    public function scopeCustom($query)
    {
        return $query->where('is_core', false);
    }

    /**
     * Scope to get privileges by category.
     */
    public function scopeInCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Check if this is a core admin privilege.
     */
    public function isCoreAdminPrivilege(): bool
    {
        $corePrivileges = ['access-hyro-admin', 'view-roles', 'create-roles', 'edit-roles', 'delete-roles'];
        return in_array($this->slug, $corePrivileges);
    }

    /**
     * Check if privilege is expired for a specific role.
     */
    public function isExpiredForRole($role): bool
    {
        $pivot = $this->roles()->where('role_id', $role->id)->first()?->pivot;

        if (!$pivot) {
            return true;
        }

        if (!$pivot->expires_at) {
            return false;
        }

        return Carbon::parse($pivot->expires_at)->isPast();
    }

    /**
     * Grant this privilege to a role.
     */
    public function grantToRole($role, $grantedBy = null, $reason = null, $expiresAt = null): void
    {
        $this->roles()->attach($role, [
            'granted_by' => $grantedBy,
            'grant_reason' => $reason,
            'expires_at' => $expiresAt,
            'granted_at' => now(),
        ]);
    }

    /**
     * Revoke this privilege from a role.
     */
    public function revokeFromRole($role): void
    {
        $this->roles()->detach($role);
    }

    /**
     * Update privilege grant for a role.
     */
    public function updateGrantForRole($role, $data): void
    {
        $this->roles()->updateExistingPivot($role, $data);
    }

    /**
     * Get the number of roles that have this privilege.
     */
    public function getRoleCountAttribute(): int
    {
        if ($this->relationLoaded('roles')) {
            return $this->roles->count();
        }

        return $this->roles()->count();
    }

    /**
     * Check if any role has this privilege.
     */
    public function hasAnyRole(): bool
    {
        return $this->role_count > 0;
    }

    /**
     * Find privilege by slug.
     */
    public static function findBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->first();
    }

    /**
     * Get all privileges grouped by category.
     */
    public static function groupedByCategory(): \Illuminate\Support\Collection
    {
        return static::orderBy('category')
            ->orderBy('name')
            ->get()
            ->groupBy('category');
    }
}
