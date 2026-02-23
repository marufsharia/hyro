<div>
    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">Avatar Settings</h2>
    
    <!-- Current Avatar Preview -->
    <div class="mb-8" wire:key="avatar-preview-{{ $avatar_type }}">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">Current Avatar</label>
        <div class="flex items-center space-x-6">
            <div class="relative">
                @if($avatarPreview)
                    <img src="{{ $avatarPreview }}" alt="Avatar Preview" class="w-32 h-32 rounded-full object-cover border-4 border-gray-200 dark:border-gray-700" wire:key="preview-temp">
                @else
                    <img src="{{ $user->avatar_url }}?t={{ time() }}" alt="{{ $user->name }}" class="w-32 h-32 rounded-full object-cover border-4 border-gray-200 dark:border-gray-700" wire:key="preview-{{ $avatar_type }}-{{ $user->id }}">
                @endif
                <div class="absolute bottom-0 right-0 w-8 h-8 bg-green-500 rounded-full border-4 border-white dark:border-gray-800"></div>
            </div>
            <div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ $user->name }}</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</p>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    Current type: <span class="font-medium capitalize text-blue-600 dark:text-blue-400">{{ $avatar_type }}</span>
                </p>
            </div>
        </div>
    </div>

    <!-- Avatar Type Selection -->
    <div class="mb-8">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">Avatar Type</label>
        
        <!-- Loading Indicator -->
        <div wire:loading wire:target="avatar_type" class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
            <div class="flex items-center">
                <svg class="animate-spin h-5 w-5 text-blue-600 dark:text-blue-400 mr-3" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-sm text-blue-700 dark:text-blue-300">Updating avatar type...</span>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            
            <!-- Upload Avatar -->
            <div class="relative">
                <input type="radio" id="avatar_upload" value="upload" wire:model.live="avatar_type" wire:click="$set('avatar_type', 'upload')" class="peer sr-only">
                <label for="avatar_upload" 
                    class="flex flex-col items-center p-6 bg-white dark:bg-gray-700 border-2 {{ $avatar_type === 'upload' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-300 dark:border-gray-600' }} rounded-lg cursor-pointer hover:border-blue-500 transition-all">
                    <svg class="w-12 h-12 {{ $avatar_type === 'upload' ? 'text-blue-500' : 'text-gray-400' }} mb-3 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span class="text-sm font-medium {{ $avatar_type === 'upload' ? 'text-blue-600 dark:text-blue-400' : 'text-gray-900 dark:text-white' }} transition-colors">Upload Image</span>
                    <span class="text-xs text-gray-500 dark:text-gray-400 mt-1">Custom avatar</span>
                </label>
                @if($avatar_type === 'upload')
                    <div class="absolute top-2 right-2 w-6 h-6 bg-blue-500 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                @endif
            </div>

            <!-- Gravatar -->
            <div class="relative">
                <input type="radio" id="avatar_gravatar" value="gravatar" wire:model.live="avatar_type" wire:click="$set('avatar_type', 'gravatar')" class="peer sr-only">
                <label for="avatar_gravatar" 
                    class="flex flex-col items-center p-6 bg-white dark:bg-gray-700 border-2 {{ $avatar_type === 'gravatar' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-300 dark:border-gray-600' }} rounded-lg cursor-pointer hover:border-blue-500 transition-all">
                    <svg class="w-12 h-12 {{ $avatar_type === 'gravatar' ? 'text-blue-500' : 'text-gray-400' }} mb-3 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-sm font-medium {{ $avatar_type === 'gravatar' ? 'text-blue-600 dark:text-blue-400' : 'text-gray-900 dark:text-white' }} transition-colors">Gravatar</span>
                    <span class="text-xs text-gray-500 dark:text-gray-400 mt-1">From gravatar.com</span>
                </label>
                @if($avatar_type === 'gravatar')
                    <div class="absolute top-2 right-2 w-6 h-6 bg-blue-500 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                @endif
            </div>

            <!-- Default Avatar -->
            <div class="relative">
                <input type="radio" id="avatar_default" value="default" wire:model.live="avatar_type" wire:click="$set('avatar_type', 'default')" class="peer sr-only">
                <label for="avatar_default" 
                    class="flex flex-col items-center p-6 bg-white dark:bg-gray-700 border-2 {{ $avatar_type === 'default' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-300 dark:border-gray-600' }} rounded-lg cursor-pointer hover:border-blue-500 transition-all">
                    <svg class="w-12 h-12 {{ $avatar_type === 'default' ? 'text-blue-500' : 'text-gray-400' }} mb-3 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span class="text-sm font-medium {{ $avatar_type === 'default' ? 'text-blue-600 dark:text-blue-400' : 'text-gray-900 dark:text-white' }} transition-colors">Default</span>
                    <span class="text-xs text-gray-500 dark:text-gray-400 mt-1">Initial-based</span>
                </label>
                @if($avatar_type === 'default')
                    <div class="absolute top-2 right-2 w-6 h-6 bg-blue-500 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                @endif
            </div>
        </div>
        
        <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
            <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            Avatar type changes automatically when you select an option
        </p>
    </div>

    <!-- Upload Avatar Form -->
    @if($avatar_type === 'upload')
        <div class="border-t border-gray-200 dark:border-gray-700 pt-8">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Upload New Avatar</h3>
            
            <form wire:submit.prevent="updateAvatar">
                <div class="space-y-4">
                    <!-- File Input -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Choose Image
                        </label>
                        <div class="flex items-center space-x-4">
                            <label class="flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                                <svg class="w-5 h-5 mr-2 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                                <span class="text-sm text-gray-700 dark:text-gray-300">Select File</span>
                                <input type="file" wire:model="newAvatar" accept="image/*" class="sr-only">
                            </label>
                            
                            @if($newAvatar)
                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ $newAvatar->getClientOriginalName() }}</span>
                            @endif
                        </div>
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            PNG, JPG, GIF up to 2MB. Recommended: 400x400px
                        </p>
                        @error('newAvatar') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <!-- Loading Indicator -->
                    <div wire:loading wire:target="newAvatar" class="text-sm text-blue-600 dark:text-blue-400">
                        Uploading...
                    </div>

                    <!-- Submit Button -->
                    @if($newAvatar)
                        <button type="submit"
                            class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                            </svg>
                            Upload Avatar
                        </button>
                    @endif
                </div>
            </form>
        </div>
    @endif

    <!-- Gravatar Info -->
    @if($avatar_type === 'gravatar')
        <div class="border-t border-gray-200 dark:border-gray-700 pt-8">
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                <div class="flex">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">About Gravatar</h3>
                        <p class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                            Your avatar is linked to your email address ({{ $user->email }}) through Gravatar. 
                            To change it, visit <a href="https://gravatar.com" target="_blank" class="underline hover:text-blue-900 dark:hover:text-blue-100">gravatar.com</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
