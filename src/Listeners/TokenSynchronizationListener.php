<?php

namespace Marufsharia\Hyro\Listeners;

use Marufsharia\Hyro\Events\RoleAssigned;
use Marufsharia\Hyro\Events\RoleRevoked;
use Marufsharia\Hyro\Events\PrivilegeGranted;
use Marufsharia\Hyro\Events\PrivilegeRevoked;
use Marufsharia\Hyro\Events\UserSuspended;
use Marufsharia\Hyro\Events\UserUnsuspended;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class TokenSynchronizationListener
{
    /**
     * Handle the RoleAssigned event.
     */
    public function handleRoleAssigned(RoleAssigned $event): void
    {
        if (!Config::get('hyro.tokens.synchronization.enabled', true)) {
            return;
        }

        try {
            $user = $event->user;
            $role = $event->role;

            // Get all active tokens for the user
            $tokens = $user->tokens()->where('revoked', false)->get();

            if ($tokens->isEmpty()) {
                return;
            }

            // Get user's updated abilities
            $newAbilities = $user->getAllAbilities()->toArray();

            // Update each token's abilities
            foreach ($tokens as $token) {
                $token->abilities = $newAbilities;
                $token->save();
            }

            Log::info('Token abilities synchronized after role assignment', [
                'user_id' => $user->id,
                'role_id' => $role->id,
                'tokens_updated' => $tokens->count(),
                'event' => 'role_assigned',
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to synchronize token abilities after role assignment', [
                'user_id' => $event->user->id ?? null,
                'role_id' => $event->role->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle the RoleRevoked event.
     */
    public function handleRoleRevoked(RoleRevoked $event): void
    {
        if (!Config::get('hyro.tokens.synchronization.enabled', true)) {
            return;
        }

        try {
            $user = $event->user;
            $role = $event->role;

            // Get all active tokens for the user
            $tokens = $user->tokens()->where('revoked', false)->get();

            if ($tokens->isEmpty()) {
                return;
            }

            // Get user's updated abilities
            $newAbilities = $user->getAllAbilities()->toArray();

            // Update each token's abilities
            foreach ($tokens as $token) {
                $token->abilities = $newAbilities;
                $token->save();
            }

            Log::info('Token abilities synchronized after role revocation', [
                'user_id' => $user->id,
                'role_id' => $role->id,
                'tokens_updated' => $tokens->count(),
                'event' => 'role_revoked',
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to synchronize token abilities after role revocation', [
                'user_id' => $event->user->id ?? null,
                'role_id' => $event->role->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle the PrivilegeGranted event.
     */
    public function handlePrivilegeGranted(PrivilegeGranted $event): void
    {
        if (!Config::get('hyro.tokens.synchronization.enabled', true)) {
            return;
        }

        try {
            $role = $event->role;
            $privilege = $event->privilege;

            // Get all users with this role
            $users = $role->users()->get();

            if ($users->isEmpty()) {
                return;
            }

            foreach ($users as $user) {
                // Get all active tokens for the user
                $tokens = $user->tokens()->where('revoked', false)->get();

                if ($tokens->isEmpty()) {
                    continue;
                }

                // Get user's updated abilities
                $newAbilities = $user->getAllAbilities()->toArray();

                // Update each token's abilities
                foreach ($tokens as $token) {
                    $token->abilities = $newAbilities;
                    $token->save();
                }
            }

            Log::info('Token abilities synchronized after privilege granted to role', [
                'role_id' => $role->id,
                'privilege_id' => $privilege->id,
                'users_affected' => $users->count(),
                'event' => 'privilege_granted',
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to synchronize token abilities after privilege grant', [
                'role_id' => $event->role->id ?? null,
                'privilege_id' => $event->privilege->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle the PrivilegeRevoked event.
     */
    public function handlePrivilegeRevoked(PrivilegeRevoked $event): void
    {
        if (!Config::get('hyro.tokens.synchronization.enabled', true)) {
            return;
        }

        try {
            $role = $event->role;
            $privilege = $event->privilege;

            // Get all users with this role
            $users = $role->users()->get();

            if ($users->isEmpty()) {
                return;
            }

            foreach ($users as $user) {
                // Get all active tokens for the user
                $tokens = $user->tokens()->where('revoked', false)->get();

                if ($tokens->isEmpty()) {
                    continue;
                }

                // Get user's updated abilities
                $newAbilities = $user->getAllAbilities()->toArray();

                // Update each token's abilities
                foreach ($tokens as $token) {
                    $token->abilities = $newAbilities;
                    $token->save();
                }
            }

            Log::info('Token abilities synchronized after privilege revoked from role', [
                'role_id' => $role->id,
                'privilege_id' => $privilege->id,
                'users_affected' => $users->count(),
                'event' => 'privilege_revoked',
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to synchronize token abilities after privilege revocation', [
                'role_id' => $event->role->id ?? null,
                'privilege_id' => $event->privilege->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle the UserSuspended event.
     */
    public function handleUserSuspended(UserSuspended $event): void
    {
        if (!Config::get('hyro.tokens.synchronization.enabled', true)) {
            return;
        }

        try {
            $user = $event->user;

            // Get all active tokens for the user
            $tokens = $user->tokens()->where('revoked', false)->get();

            if ($tokens->isEmpty()) {
                return;
            }

            // Revoke all tokens for suspended user
            foreach ($tokens as $token) {
                $token->update([
                    'revoked' => true,
                    'revoked_at' => now(),
                    'revocation_reason' => 'User suspended',
                ]);
            }

            Log::info('Tokens revoked after user suspension', [
                'user_id' => $user->id,
                'tokens_revoked' => $tokens->count(),
                'event' => 'user_suspended',
                'reason' => $event->metadata['reason'] ?? null,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to revoke tokens after user suspension', [
                'user_id' => $event->user->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle the UserUnsuspended event.
     */
    public function handleUserUnsuspended(UserUnsuspended $event): void
    {
        if (!Config::get('hyro.tokens.synchronization.enabled', true)) {
            return;
        }

        try {
            $user = $event->user;

            // Note: We don't automatically restore tokens when a user is unsuspended
            // The user will need to generate new tokens
            Log::info('User unsuspended - tokens remain revoked', [
                'user_id' => $user->id,
                'event' => 'user_unsuspended',
                'note' => 'User must generate new tokens',
                'reason' => $event->metadata['reason'] ?? null,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to handle user unsuspension for tokens', [
                'user_id' => $event->user->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle multiple events via event subscriber.
     */
    public function subscribe($events): array
    {
        return [
            RoleAssigned::class => 'handleRoleAssigned',
            RoleRevoked::class => 'handleRoleRevoked',
            PrivilegeGranted::class => 'handlePrivilegeGranted',
            PrivilegeRevoked::class => 'handlePrivilegeRevoked',
            UserSuspended::class => 'handleUserSuspended',
            UserUnsuspended::class => 'handleUserUnsuspended',
        ];
    }
}
