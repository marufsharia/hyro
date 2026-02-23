<?php

namespace Marufsharia\Hyro\Core\Traits;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;
use Marufsharia\Hyro\Core\Models\UserActivityLog;

trait HasProfileManagement
{
    /**
     * Get user's avatar URL with priority: upload → gravatar → default
     */
    public function getAvatarUrlAttribute(): string
    {
        // Check avatar_type preference
        if ($this->avatar_type === 'upload' && $this->avatar && Storage::disk('public')->exists($this->avatar)) {
            // Priority 1: Uploaded image (if type is upload and file exists)
            return Storage::disk('public')->url($this->avatar);
        } elseif ($this->avatar_type === 'gravatar') {
            // Priority 2: Gravatar (if type is gravatar)
            return $this->getGravatarUrl();
        } elseif ($this->avatar_type === 'default') {
            // Priority 3: Default avatar (if type is default)
            return $this->getDefaultAvatarUrl();
        }
        
        // Fallback logic if avatar_type is not set or invalid
        // Try uploaded image first
        if ($this->avatar && Storage::disk('public')->exists($this->avatar)) {
            return Storage::disk('public')->url($this->avatar);
        }
        
        // Then gravatar
        if ($this->email) {
            return $this->getGravatarUrl();
        }
        
        // Finally default
        return $this->getDefaultAvatarUrl();
    }
    
    /**
     * Get Gravatar URL
     */
    public function getGravatarUrl(int $size = 200): string
    {
        $hash = md5(strtolower(trim($this->email)));
        return "https://www.gravatar.com/avatar/{$hash}?s={$size}&d=mp";
    }
    
    /**
     * Get default avatar URL
     */
    public function getDefaultAvatarUrl(): string
    {
        $initial = strtoupper(substr($this->name, 0, 1));
        $colors = ['#3B82F6', '#8B5CF6', '#EC4899', '#10B981', '#F59E0B', '#EF4444'];
        $color = $colors[ord($initial) % count($colors)];
        
        return "https://ui-avatars.com/api/?name=" . urlencode($this->name) . 
               "&size=200&background=" . substr($color, 1) . "&color=fff&bold=true";
    }
    
    /**
     * Update profile information
     */
    public function updateProfile(array $data): bool
    {
        $this->fill($data);
        return $this->save();
    }
    
    /**
     * Change password
     */
    public function changePassword(string $newPassword): bool
    {
        $this->password = Hash::make($newPassword);
        $this->password_changed_at = now();
        $this->force_password_change = false;
        
        return $this->save();
    }
    
    /**
     * Enable two-factor authentication
     */
    public function enableTwoFactor(): array
    {
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();
        
        $this->two_factor_secret = encrypt($secret);
        $this->two_factor_enabled = false; // Will be enabled after confirmation
        $this->save();
        
        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            $this->email,
            $secret
        );
        
        // Generate QR code as inline SVG using Google Charts API
        $qrCodeImage = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($qrCodeUrl);
        
        return [
            'secret' => $secret,
            'qr_code' => $qrCodeImage,
            'recovery_codes' => $this->generateRecoveryCodes()
        ];
    }
    
    /**
     * Confirm two-factor authentication
     */
    public function confirmTwoFactor(string $code): bool
    {
        if (!$this->two_factor_secret) {
            return false;
        }
        
        $google2fa = new Google2FA();
        $secret = decrypt($this->two_factor_secret);
        
        if ($google2fa->verifyKey($secret, $code)) {
            $this->two_factor_enabled = true;
            $this->two_factor_confirmed_at = now();
            $this->save();
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Disable two-factor authentication
     */
    public function disableTwoFactor(): bool
    {
        $this->two_factor_enabled = false;
        $this->two_factor_secret = null;
        $this->two_factor_recovery_codes = null;
        $this->two_factor_confirmed_at = null;
        
        return $this->save();
    }
    
    /**
     * Verify two-factor code
     */
    public function verifyTwoFactorCode(string $code): bool
    {
        if (!$this->two_factor_enabled || !$this->two_factor_secret) {
            return false;
        }
        
        $google2fa = new Google2FA();
        $secret = decrypt($this->two_factor_secret);
        
        return $google2fa->verifyKey($secret, $code, 2); // 2 = tolerance window
    }
    
    /**
     * Verify recovery code
     */
    public function verifyRecoveryCode(string $code): bool
    {
        if (!$this->two_factor_recovery_codes) {
            return false;
        }
        
        $recoveryCodes = is_string($this->two_factor_recovery_codes) 
            ? json_decode($this->two_factor_recovery_codes, true) 
            : $this->two_factor_recovery_codes;
        
        if (in_array($code, $recoveryCodes)) {
            // Remove used recovery code
            $recoveryCodes = array_diff($recoveryCodes, [$code]);
            $this->two_factor_recovery_codes = array_values($recoveryCodes);
            $this->save();
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Generate recovery codes
     */
    public function generateRecoveryCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = Str::random(10) . '-' . Str::random(10);
        }
        
        $this->two_factor_recovery_codes = $codes;
        $this->save();
        
        return $codes;
    }
    
    /**
     * Request account deletion
     */
    public function requestDeletion(string $reason = null, int $daysUntilDeletion = 30): bool
    {
        $this->deletion_requested_at = now();
        $this->deletion_scheduled_at = now()->addDays($daysUntilDeletion);
        $this->deletion_reason = $reason;
        
        return $this->save();
    }
    
    /**
     * Cancel account deletion
     */
    public function cancelDeletion(): bool
    {
        $this->deletion_requested_at = null;
        $this->deletion_scheduled_at = null;
        $this->deletion_reason = null;
        
        return $this->save();
    }
    
    /**
     * Check if account deletion is pending
     */
    public function isDeletionPending(): bool
    {
        return $this->deletion_requested_at !== null;
    }
    
    /**
     * Get days until deletion
     */
    public function getDaysUntilDeletion(): ?int
    {
        if (!$this->deletion_scheduled_at) {
            return null;
        }
        
        return now()->diffInDays($this->deletion_scheduled_at, false);
    }
    
    /**
     * Log activity
     */
    public function logActivity(string $action, array $metadata = []): void
    {
        $this->activityLogs()->create([
            'action' => $action,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => $metadata,
        ]);
    }
    
    /**
     * Activity logs relationship
     */
    public function activityLogs()
    {
        return $this->hasMany(UserActivityLog::class);
    }
    
    /**
     * Get user preferences
     */
    public function getPreference(string $key, $default = null)
    {
        $preferences = $this->preferences ?? [];
        return $preferences[$key] ?? $default;
    }
    
    /**
     * Set user preference
     */
    public function setPreference(string $key, $value): bool
    {
        $preferences = $this->preferences ?? [];
        $preferences[$key] = $value;
        $this->preferences = $preferences;
        
        return $this->save();
    }
    
    /**
     * Process pending deletions (should be run daily)
     */
    public static function processPendingDeletions(): int
    {
        $count = 0;
        $users = static::where('deletion_scheduled_at', '<=', now())
            ->whereNotNull('deletion_requested_at')
            ->get();
        
        foreach ($users as $user) {
            $user->delete();
            $count++;
        }
        
        return $count;
    }
}

