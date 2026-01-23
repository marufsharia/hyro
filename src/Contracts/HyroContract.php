<?php

namespace MarufSharia\Hyro\Contracts;

interface HyroContract
{
    public function user(): UserContract;
    
    public function role(): RoleContract;
    
    public function privilege(): PrivilegeContract;
    
    public function audit(): AuditLogger;
    
    public function install(): bool;
    
    public function uninstall(bool $force = false): bool;
    
    public function status(): array;
    
    public function suspendUser(int $userId, string $reason): void;
    
    public function unsuspendUser(int $userId): void;
    
    public function assignRole(int $userId, string $role): void;
    
    public function revokeRole(int $userId, string $role): void;
    
    public function assignPrivilege(int $userId, string $privilege): void;
    
    public function revokePrivilege(int $userId, string $privilege): void;
    
    public function clearCache(): void;
    
    public function emergencyLockdown(): void;
    
    public function emergencyUnlock(): void;
}