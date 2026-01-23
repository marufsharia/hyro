<?php

namespace Marufsharia\Hyro\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Laravel\Sanctum\HasApiTokens;
use Marufsharia\Hyro\Contracts\AuthorizationResolverContract;
use Marufsharia\Hyro\Events\TokenAbilitiesSynced;
use Marufsharia\Hyro\Events\TokenRevoked;
use Marufsharia\Hyro\Models\AuditLog;

class TokenSynchronizationService
{
    /**
     * The authorization resolver.
     */
    private AuthorizationResolverContract $authorizationResolver;

    /**
     * Whether token synchronization is enabled.
     */
    private bool $enabled;

    public function __construct(AuthorizationResolverContract $authorizationResolver)
    {
        $this->authorizationResolver = $authorizationResolver;
        $this->enabled = Config::get('hyro.tokens.synchronization.enabled', true);
    }

    /**
     * Sync abilities for all of a user's tokens.
     */
    public function syncUserTokens($user): void
    {
        if (!$this->enabled || !$this->userUsesSanctum($user)) {
            return;
        }

        $abilities = $this->authorizationResolver->getAbilitiesForUser($user);

        foreach ($user->tokens as $token) {
            $this->syncToken($token, $abilities);
        }
    }

    /**
     * Sync abilities for a specific token.
     */
    public function syncToken($token, ?array $abilities = null): void
    {
        if (!$this->enabled) {
            return;
        }

        if ($abilities === null) {
            $user = $token->tokenable;
            $abilities = $this->authorizationResolver->getAbilitiesForUser($user);
        }

        // Filter out wildcard abilities (Sanctum doesn't support them)
        $sanitizedAbilities = array_filter($abilities, function ($ability) {
            return !str_contains($ability, '*');
        });

        // Update token abilities
        $oldAbilities = $token->abilities ?? [];
        $token->abilities = $sanitizedAbilities;
        $token->save();

        // Fire event
        event(new TokenAbilitiesSynced($token, $oldAbilities, $sanitizedAbilities));

        // Log the sync
        if (Config::get('hyro.auditing.enabled', true)) {
            AuditLog::log('token_abilities_synced', $token->tokenable, $token, [
                'old' => $oldAbilities,
                'new' => $sanitizedAbilities,
            ]);
        }
    }

    /**
     * Revoke all tokens for a user.
     */
    public function revokeUserTokens($user): void
    {
        if (!$this->enabled || !$this->userUsesSanctum($user)) {
            return;
        }

        $tokens = $user->tokens;

        foreach ($tokens as $token) {
            $this->revokeToken($token);
        }

        // Log the revocation
        if (Config::get('hyro.auditing.enabled', true)) {
            AuditLog::log('user_tokens_revoked', $user, null, [
                'token_count' => $tokens->count(),
                'reason' => 'user_suspension_or_privilege_change',
            ]);
        }
    }

    /**
     * Revoke a specific token.
     */
    public function revokeToken($token, ?string $reason = null): void
    {
        if (!$this->enabled) {
            return;
        }

        $token->delete();

        // Fire event
        event(new TokenRevoked($token, $reason));

        // Log the revocation
        if (Config::get('hyro.auditing.enabled', true)) {
            AuditLog::log('token_revoked', $token->tokenable, $token, [
                'reason' => $reason,
            ]);
        }
    }

    /**
     * Check if a user's privileges have changed and sync tokens if needed.
     */
    public function checkAndSyncIfNeeded($user): void
    {
        if (!$this->enabled || !$this->userUsesSanctum($user)) {
            return;
        }

        $currentAbilities = $this->authorizationResolver->getAbilitiesForUser($user);

        foreach ($user->tokens as $token) {
            $tokenAbilities = $token->abilities ?? [];

            // Sort both arrays for comparison
            sort($currentAbilities);
            sort($tokenAbilities);

            if ($currentAbilities != $tokenAbilities) {
                $this->syncToken($token, $currentAbilities);
            }
        }
    }

    /**
     * Create a new token with synchronized abilities.
     */
    public function createToken($user, string $name, array $abilities = [], ?\DateTimeInterface $expiresAt = null): string
    {
        if (!$this->enabled || !$this->userUsesSanctum($user)) {
            return $user->createToken($name, $abilities, $expiresAt)->plainTextToken;
        }

        // Merge user abilities with requested abilities
        $userAbilities = $this->authorizationResolver->getAbilitiesForUser($user);
        $mergedAbilities = array_unique(array_merge($userAbilities, $abilities));

        // Filter out wildcards
        $sanitizedAbilities = array_filter($mergedAbilities, function ($ability) {
            return !str_contains($ability, '*');
        });

        return $user->createToken($name, $sanitizedAbilities, $expiresAt)->plainTextToken;
    }

    /**
     * Check if user uses Sanctum.
     */
    private function userUsesSanctum($user): bool
    {
        return in_array(HasApiTokens::class, class_uses_recursive($user));
    }

    /**
     * Get token synchronization status for a user.
     */
    public function getSyncStatus($user): array
    {
        if (!$this->userUsesSanctum($user)) {
            return ['enabled' => false, 'message' => 'User does not use Sanctum'];
        }

        $status = [
            'enabled' => $this->enabled,
            'token_count' => $user->tokens->count(),
            'synced_tokens' => 0,
            'unsynced_tokens' => 0,
        ];

        $userAbilities = $this->authorizationResolver->getAbilitiesForUser($user);
        $sanitizedUserAbilities = array_filter($userAbilities, function ($ability) {
            return !str_contains($ability, '*');
        });

        foreach ($user->tokens as $token) {
            $tokenAbilities = $token->abilities ?? [];
            sort($tokenAbilities);
            sort($sanitizedUserAbilities);

            if ($tokenAbilities == $sanitizedUserAbilities) {
                $status['synced_tokens']++;
            } else {
                $status['unsynced_tokens']++;
            }
        }

        return $status;
    }
}
