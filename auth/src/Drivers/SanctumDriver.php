<?php

namespace Marufsharia\Hyro\Auth\Drivers;

use Marufsharia\Hyro\Core\Repositories\UserRepository;
use Marufsharia\Hyro\Core\Repositories\RoleRepository;
use Marufsharia\Hyro\Core\Repositories\PrivilegeRepository;
use Marufsharia\Hyro\Core\Repositories\AuditRepository;

class SanctumDriver
{
    protected array $config;
    protected ?UserRepository $userRepository = null;
    protected ?RoleRepository $roleRepository = null;
    protected ?PrivilegeRepository $privilegeRepository = null;
    protected ?AuditRepository $auditRepository = null;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Get user repository instance
     */
    public function user(): UserRepository
    {
        if (!$this->userRepository) {
            $this->userRepository = new UserRepository();
        }

        return $this->userRepository;
    }

    /**
     * Get role repository instance
     */
    public function role(): RoleRepository
    {
        if (!$this->roleRepository) {
            $this->roleRepository = new RoleRepository();
        }

        return $this->roleRepository;
    }

    /**
     * Get privilege repository instance
     */
    public function privilege(): PrivilegeRepository
    {
        if (!$this->privilegeRepository) {
            $this->privilegeRepository = new PrivilegeRepository();
        }

        return $this->privilegeRepository;
    }

    /**
     * Get audit repository instance
     */
    public function audit(): AuditRepository
    {
        if (!$this->auditRepository) {
            $this->auditRepository = new AuditRepository();
        }

        return $this->auditRepository;
    }

    /**
     * Install Hyro resources
     */
    public function install(): bool
    {
        // Run migrations
        \Artisan::call('migrate', [
            '--path' => 'vendor/marufsharia/hyro/database/migrations',
            '--force' => true,
        ]);

        // Create default roles and privileges
        $this->createDefaultRoles();
        $this->createDefaultPrivileges();

        return true;
    }

    /**
     * Uninstall Hyro resources
     */
    public function uninstall(bool $force = false): bool
    {
        if (!$force) {
            return false; // Prevent accidental uninstall
        }

        // Rollback migrations
        \Artisan::call('migrate:rollback', [
            '--path' => 'vendor/marufsharia/hyro/database/migrations',
            '--force' => true,
        ]);

        return true;
    }

    /**
     * Create default roles
     */
    protected function createDefaultRoles(): void
    {
        $roles = [
            ['name' => 'Super Admin', 'slug' => 'super-admin', 'description' => 'Full system access'],
            ['name' => 'Admin', 'slug' => 'admin', 'description' => 'Administrative access'],
            ['name' => 'User', 'slug' => 'user', 'description' => 'Basic user access'],
        ];

        foreach ($roles as $role) {
            $this->role()->create($role);
        }
    }

    /**
     * Create default privileges
     */
    protected function createDefaultPrivileges(): void
    {
        $privileges = [
            ['name' => 'View Users', 'slug' => 'view-users', 'category' => 'users'],
            ['name' => 'Create Users', 'slug' => 'create-users', 'category' => 'users'],
            ['name' => 'Edit Users', 'slug' => 'edit-users', 'category' => 'users'],
            ['name' => 'Delete Users', 'slug' => 'delete-users', 'category' => 'users'],
            ['name' => 'View Roles', 'slug' => 'view-roles', 'category' => 'roles'],
            ['name' => 'Create Roles', 'slug' => 'create-roles', 'category' => 'roles'],
            ['name' => 'Edit Roles', 'slug' => 'edit-roles', 'category' => 'roles'],
            ['name' => 'Delete Roles', 'slug' => 'delete-roles', 'category' => 'roles'],
        ];

        foreach ($privileges as $privilege) {
            $this->privilege()->create($privilege);
        }
    }
}
