<?php

namespace Marufsharia\Hyro\AdminPanel\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Marufsharia\Hyro\Core\Traits\WithAlerts;

class ProfileManager extends Component
{
    use WithFileUploads, WithAlerts;

    public $activeTab = 'profile';
    
    // Profile Information
    public $name;
    public $email;
    public $phone;
    public $bio;
    public $timezone;
    public $locale;
    
    // Avatar
    public $avatar;
    public $avatar_type;
    public $newAvatar;
    public $avatarPreview;
    
    // Password Change
    public $current_password;
    public $new_password;
    public $new_password_confirmation;
    
    // Two-Factor Authentication
    public $two_factor_enabled;
    public $two_factor_code;
    public $two_factor_qr_code;
    public $two_factor_secret;
    public $recovery_codes = [];
    public $showRecoveryCodes = false;
    
    // Account Deletion
    public $deletion_reason;
    public $deletion_confirmation;
    public $deletion_password;
    
    protected $listeners = ['refreshComponent' => '$refresh'];

    public function mount()
    {
        $user = Auth::user();
        
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone;
        $this->bio = $user->bio;
        $this->timezone = $user->timezone ?? 'UTC';
        $this->locale = $user->locale ?? 'en';
        $this->avatar_type = $user->avatar_type ?? 'default';
        $this->two_factor_enabled = $user->two_factor_enabled ?? false;
    }

    public function updatedNewAvatar()
    {
        try {
            $this->validate([
                'newAvatar' => 'image|max:2048', // 2MB Max
            ]);

            $this->avatarPreview = $this->newAvatar->temporaryUrl();
        } catch (\Exception $e) {
            $this->toastError('Error uploading file: ' . $e->getMessage());
            \Log::error('Avatar upload error: ' . $e->getMessage());
        }
    }

    public function updateProfile()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . Auth::id(),
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:500',
            'timezone' => 'required|string',
            'locale' => 'required|string',
        ]);

        $user = Auth::user();
        
        $user->update([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'bio' => $this->bio,
            'timezone' => $this->timezone,
            'locale' => $this->locale,
        ]);
        
        $user->logActivity('profile_updated', [
            'fields' => ['name', 'email', 'phone', 'bio', 'timezone', 'locale']
        ]);

        $this->toastSuccess('Profile updated successfully!');
        $this->dispatch('profile-updated');
    }

    public function updateAvatar()
    {
        try {
            if (!$this->newAvatar) {
                $this->toastError('Please select an image to upload.');
                return;
            }

            $this->validate([
                'newAvatar' => 'required|image|max:2048',
            ]);

            $user = Auth::user();
            
            // Delete old avatar if exists
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            
            // Store new avatar
            $path = $this->newAvatar->store('avatars', 'public');
            
            if (!$path) {
                throw new \Exception('Failed to store avatar file');
            }
            
            $user->update([
                'avatar' => $path,
                'avatar_type' => 'upload', // Automatically set to upload
            ]);
            
            $user->logActivity('avatar_updated', ['type' => 'upload', 'path' => $path]);
            
            $this->newAvatar = null;
            $this->avatarPreview = null;
            $this->avatar_type = 'upload';
            
            $this->toastSuccess('Avatar uploaded successfully!');
            
            // Dispatch global event to refresh all components
            $this->dispatch('avatar-updated')->to('hyro::admin.header');
            $this->dispatch('avatar-updated')->to('hyro::admin.sidebar');
            
            // Also dispatch a browser event to force page refresh if needed
            $this->dispatch('avatarChanged');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Re-throw validation exceptions
            throw $e;
        } catch (\Exception $e) {
            $this->toastError('Failed to upload avatar: ' . $e->getMessage());
            \Log::error('Avatar upload failed: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function updatedAvatarType($value)
    {
        // Only proceed if the value actually changed from what's in the database
        $user = Auth::user();
        
        if ($user->avatar_type === $value) {
            // Value hasn't changed, skip processing
            return;
        }
        
        // Update the local property
        $this->avatar_type = $value;

        // Call the setter to save to database
        $this->setAvatarType($value);
    }

    public function setAvatarType($type)
    {
        if (!in_array($type, ['upload', 'gravatar', 'default'])) {
            return;
        }

        $user = Auth::user();
     
        
        // If switching away from upload, optionally keep the uploaded file
        // (don't delete it, just change the type)
        
        $updated = $user->update([
            'avatar_type' => $type,
        ]);
        
        if (!$updated) {
            $this->toastError('Failed to update avatar type. Please try again.');
            return;
        }
        
        // Refresh user to get updated avatar_url
        $user->refresh();
        
        $this->avatar_type = $type;
        
        $user->logActivity('avatar_type_changed', ['type' => $type]);
        
        $this->toastSuccess('Avatar type changed to ' . ucfirst($type) . '!');
        
        // Dispatch to specific components
        $this->dispatch('avatar-updated')->to('hyro::admin.header');
        $this->dispatch('avatar-updated')->to('hyro::admin.sidebar');
        
        // Dispatch browser event to reload page after showing toast
        //$this->dispatch('avatarChanged');
    }

    public function changePassword()
    {
        $this->validate([
            'current_password' => 'required',
            'new_password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        $user = Auth::user();

        if (!Hash::check($this->current_password, $user->password)) {
            $this->addError('current_password', 'The current password is incorrect.');
            return;
        }

        $user->changePassword($this->new_password);
        
        $user->logActivity('password_changed');

        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);
        
        $this->toastSuccess('Password changed successfully!');
        $this->dispatch('password-changed');
    }

    public function enableTwoFactor()
    {
        $user = Auth::user();
        
        $data = $user->enableTwoFactor();
        
        $this->two_factor_qr_code = $data['qr_code'];
        $this->two_factor_secret = $data['secret'];
        $this->recovery_codes = $data['recovery_codes'];
        $this->showRecoveryCodes = true;
        
        $this->toastInfo('Scan the QR code with your authenticator app and enter the code to confirm.');
    }

    public function confirmTwoFactor()
    {
        $this->validate([
            'two_factor_code' => 'required|string|size:6',
        ]);

        $user = Auth::user();

        if ($user->confirmTwoFactor($this->two_factor_code)) {
            $this->two_factor_enabled = true;
            $this->reset(['two_factor_code', 'two_factor_qr_code', 'two_factor_secret']);
            
            $user->logActivity('two_factor_enabled');
            
            $this->toastSuccess('Two-factor authentication enabled successfully!');
            $this->dispatch('two-factor-enabled');
        } else {
            $this->addError('two_factor_code', 'The verification code is invalid.');
        }
    }

    public function disableTwoFactor()
    {
        $user = Auth::user();
        
        $user->disableTwoFactor();
        
        $this->two_factor_enabled = false;
        $this->reset(['two_factor_code', 'recovery_codes', 'showRecoveryCodes']);
        
        $user->logActivity('two_factor_disabled');
        
        $this->toastSuccess('Two-factor authentication disabled successfully!');
        $this->dispatch('two-factor-disabled');
    }

    public function regenerateRecoveryCodes()
    {
        $user = Auth::user();
        
        $this->recovery_codes = $user->generateRecoveryCodes();
        $this->showRecoveryCodes = true;
        
        $user->logActivity('recovery_codes_regenerated');
        
        $this->toastSuccess('Recovery codes regenerated successfully!');
    }

    public function requestAccountDeletion()
    {
        $this->validate([
            'deletion_reason' => 'required|string|max:500',
            'deletion_confirmation' => 'required|accepted',
            'deletion_password' => 'required',
        ]);

        $user = Auth::user();

        if (!Hash::check($this->deletion_password, $user->password)) {
            $this->addError('deletion_password', 'The password is incorrect.');
            return;
        }

        $user->requestDeletion($this->deletion_reason);
        
        $user->logActivity('account_deletion_requested', [
            'reason' => $this->deletion_reason,
            'scheduled_at' => $user->deletion_scheduled_at,
        ]);

        $this->reset(['deletion_reason', 'deletion_confirmation', 'deletion_password']);
        
        $this->toastWarning('Account deletion requested. Your account will be deleted in 30 days.');
        $this->dispatch('deletion-requested');
    }

    public function cancelAccountDeletion()
    {
        $user = Auth::user();
        
        $user->cancelDeletion();
        
        $user->logActivity('account_deletion_cancelled');
        
        $this->toastSuccess('Account deletion cancelled successfully!');
        $this->dispatch('deletion-cancelled');
    }

    public function render()
    {
        $user = Auth::user();
        
        return view('hyro::profile.profile-manager', [
            'user' => $user,
            'timezones' => timezone_identifiers_list(),
            'locales' => config('app.available_locales', ['en' => 'English']),
        ])->layout('hyro::admin.layouts.app');
    }
}
