<div>
    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">Two-Factor Authentication</h2>
    
    <!-- 2FA Status -->
    <div class="mb-8">
        <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
            <div class="flex items-center">
                @if($two_factor_enabled)
                    <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Enabled</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Two-factor authentication is active</p>
                    </div>
                @else
                    <div class="w-12 h-12 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Disabled</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Two-factor authentication is not active</p>
                    </div>
                @endif
            </div>
            
            @if($two_factor_enabled)
                <button wire:click="disableTwoFactor" type="button"
                    class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Disable 2FA
                </button>
            @else
                <button wire:click="enableTwoFactor" type="button"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    Enable 2FA
                </button>
            @endif
        </div>
    </div>

    <!-- What is 2FA -->
    <div class="mb-8 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6">
        <div class="flex">
            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">What is Two-Factor Authentication?</h3>
                <p class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                    Two-factor authentication adds an extra layer of security to your account. When enabled, you'll need to enter a 6-digit code from your authenticator app in addition to your password when logging in.
                </p>
                <p class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                    Recommended apps: Google Authenticator, Authy, Microsoft Authenticator
                </p>
            </div>
        </div>
    </div>

    <!-- Setup 2FA -->
    @if($two_factor_qr_code && !$two_factor_enabled)
        <div class="border-t border-gray-200 dark:border-gray-700 pt-8">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-6">Setup Two-Factor Authentication</h3>
            
            <div class="space-y-6">
                <!-- Step 1: Scan QR Code -->
                <div>
                    <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Step 1: Scan QR Code</h4>
                    <div class="bg-white dark:bg-gray-700 p-6 rounded-lg border border-gray-200 dark:border-gray-600 inline-block">
                        <img src="{{ $two_factor_qr_code }}" alt="2FA QR Code" class="w-48 h-48">
                    </div>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Scan this QR code with your authenticator app
                    </p>
                </div>

                <!-- Manual Entry -->
                @if($two_factor_secret)
                    <div>
                        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Or enter manually:</h4>
                        <div class="flex items-center space-x-2">
                            <code class="px-4 py-2 bg-gray-100 dark:bg-gray-700 rounded-lg text-sm font-mono">
                                {{ $two_factor_secret }}
                            </code>
                            <button type="button" onclick="navigator.clipboard.writeText('{{ $two_factor_secret }}')"
                                class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                @endif

                <!-- Step 2: Verify Code -->
                <form wire:submit.prevent="confirmTwoFactor">
                    <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Step 2: Verify Code</h4>
                    <div class="flex items-end space-x-4">
                        <div class="flex-1">
                            <input type="text" wire:model="two_factor_code" maxlength="6" 
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white text-center text-2xl tracking-widest font-mono"
                                placeholder="000000">
                            @error('two_factor_code') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                        </div>
                        <button type="submit"
                            class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors duration-200">
                            Verify & Enable
                        </button>
                    </div>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Enter the 6-digit code from your authenticator app
                    </p>
                </form>

                <!-- Recovery Codes -->
                @if($showRecoveryCodes && count($recovery_codes) > 0)
                    <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-6">
                        <div class="flex items-start">
                            <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <div class="ml-3 flex-1">
                                <h4 class="text-sm font-medium text-yellow-800 dark:text-yellow-200 mb-2">Save Your Recovery Codes</h4>
                                <p class="text-sm text-yellow-700 dark:text-yellow-300 mb-4">
                                    Store these recovery codes in a safe place. You can use them to access your account if you lose your authenticator device.
                                </p>
                                <div class="grid grid-cols-2 gap-2 bg-white dark:bg-gray-800 p-4 rounded-lg">
                                    @foreach($recovery_codes as $code)
                                        <code class="text-sm font-mono text-gray-900 dark:text-white">{{ $code }}</code>
                                    @endforeach
                                </div>
                                <button type="button" onclick="window.print()"
                                    class="mt-4 inline-flex items-center px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                    </svg>
                                    Print Recovery Codes
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Manage 2FA (When Enabled) -->
    @if($two_factor_enabled)
        <div class="border-t border-gray-200 dark:border-gray-700 pt-8">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-6">Manage Two-Factor Authentication</h3>
            
            <!-- Regenerate Recovery Codes -->
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-6">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Recovery Codes</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            If you lose access to your authenticator app, you can use recovery codes to sign in. Each code can only be used once.
                        </p>
                    </div>
                    <button wire:click="regenerateRecoveryCodes" type="button"
                        class="ml-4 inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Regenerate
                    </button>
                </div>

                <!-- Show Recovery Codes After Regeneration -->
                @if($showRecoveryCodes && count($recovery_codes) > 0)
                    <div class="mt-6 bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                        <h5 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Your New Recovery Codes</h5>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($recovery_codes as $code)
                                <code class="text-sm font-mono text-gray-900 dark:text-white">{{ $code }}</code>
                            @endforeach
                        </div>
                        <p class="mt-3 text-xs text-red-600 dark:text-red-400">
                            ⚠️ Make sure to save these codes. They won't be shown again.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
