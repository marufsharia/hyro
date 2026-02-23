<div>
    <h2 class="text-xl font-semibold text-red-600 dark:text-red-400 mb-6">Danger Zone</h2>
    
    <div class="bg-red-50 dark:bg-red-900/20 border-2 border-red-200 dark:border-red-800 rounded-lg p-6">
        <div class="flex items-start">
            <svg class="w-6 h-6 text-red-600 dark:text-red-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <div class="ml-3 flex-1">
                <h3 class="text-lg font-medium text-red-800 dark:text-red-200 mb-2">Warning</h3>
                <p class="text-sm text-red-700 dark:text-red-300">
                    Actions in this section are irreversible. Please proceed with caution.
                </p>
            </div>
        </div>
    </div>

    <!-- Account Deletion Status -->
    @if($user->isDeletionPending())
        <div class="mt-8 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-6">
            <div class="flex items-start">
                <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <div class="ml-3 flex-1">
                    <h3 class="text-lg font-medium text-yellow-800 dark:text-yellow-200 mb-2">Account Deletion Pending</h3>
                    <p class="text-sm text-yellow-700 dark:text-yellow-300 mb-4">
                        Your account is scheduled for deletion in <strong>{{ $user->getDaysUntilDeletion() }} days</strong>.
                    </p>
                    <p class="text-sm text-yellow-700 dark:text-yellow-300 mb-4">
                        Scheduled deletion date: <strong>{{ $user->deletion_scheduled_at->format('F d, Y') }}</strong>
                    </p>
                    @if($user->deletion_reason)
                        <p class="text-sm text-yellow-700 dark:text-yellow-300 mb-4">
                            Reason: {{ $user->deletion_reason }}
                        </p>
                    @endif
                    <button wire:click="cancelAccountDeletion" type="button"
                        class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Cancel Deletion
                    </button>
                </div>
            </div>
        </div>
    @else
        <!-- Delete Account Form -->
        <div class="mt-8">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Delete Account</h3>
            
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6">
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                    Once you delete your account, there is no going back. Your account will be scheduled for permanent deletion in 30 days. During this period, you can cancel the deletion request.
                </p>

                <form wire:submit.prevent="requestAccountDeletion" class="space-y-6">
                    <!-- Deletion Reason -->
                    <div>
                        <label for="deletion_reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Reason for Deletion <span class="text-red-500">*</span>
                        </label>
                        <textarea id="deletion_reason" wire:model="deletion_reason" rows="4"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                            placeholder="Please tell us why you're leaving..."></textarea>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            This helps us improve our service
                        </p>
                        @error('deletion_reason') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <!-- Confirmation Checkbox -->
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input id="deletion_confirmation" type="checkbox" wire:model="deletion_confirmation"
                                class="w-4 h-4 text-red-600 bg-gray-100 border-gray-300 rounded focus:ring-red-500 dark:focus:ring-red-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                        </div>
                        <label for="deletion_confirmation" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                            I understand that this action will schedule my account for permanent deletion in 30 days, and all my data will be permanently removed.
                        </label>
                    </div>
                    @error('deletion_confirmation') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror

                    <!-- Password Confirmation -->
                    <div>
                        <label for="deletion_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Confirm Your Password <span class="text-red-500">*</span>
                        </label>
                        <input type="password" id="deletion_password" wire:model="deletion_password"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                            placeholder="Enter your password to confirm">
                        @error('deletion_password') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <!-- What Will Be Deleted -->
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">What will be deleted:</h4>
                        <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                            <li class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                                Your profile information
                            </li>
                            <li class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                                All your content and data
                            </li>
                            <li class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                                Your activity history
                            </li>
                            <li class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                                Your preferences and settings
                            </li>
                        </ul>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-center justify-end pt-4 border-t border-gray-200 dark:border-gray-700">
                        <button type="submit"
                            class="inline-flex items-center px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors duration-200">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Delete My Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Additional Information -->
    <div class="mt-8 bg-gray-50 dark:bg-gray-700/50 rounded-lg p-6">
        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Important Information</h4>
        <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
            <li class="flex items-start">
                <svg class="w-5 h-5 mr-2 text-blue-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <span>You have 30 days to cancel the deletion request</span>
            </li>
            <li class="flex items-start">
                <svg class="w-5 h-5 mr-2 text-blue-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <span>After 30 days, your account and all data will be permanently deleted</span>
            </li>
            <li class="flex items-start">
                <svg class="w-5 h-5 mr-2 text-blue-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <span>You will receive email notifications before the final deletion</span>
            </li>
            <li class="flex items-start">
                <svg class="w-5 h-5 mr-2 text-blue-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <span>Some information may be retained for legal or security purposes</span>
            </li>
        </ul>
    </div>
</div>
