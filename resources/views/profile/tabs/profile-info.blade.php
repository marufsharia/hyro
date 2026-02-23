<div>
    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">Profile Information</h2>
    
    <form wire:submit.prevent="updateProfile" class="space-y-6">
        <!-- Name -->
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Full Name <span class="text-red-500">*</span>
            </label>
            <input type="text" id="name" wire:model="name"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                placeholder="Enter your full name">
            @error('name') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
        </div>

        <!-- Email -->
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Email Address <span class="text-red-500">*</span>
            </label>
            <input type="email" id="email" wire:model="email"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                placeholder="your.email@example.com">
            @error('email') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
        </div>

        <!-- Phone -->
        <div>
            <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Phone Number
            </label>
            <input type="tel" id="phone" wire:model="phone"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                placeholder="+1 (555) 123-4567">
            @error('phone') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
        </div>

        <!-- Bio -->
        <div>
            <label for="bio" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Bio
            </label>
            <textarea id="bio" wire:model="bio" rows="4"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                placeholder="Tell us about yourself..."></textarea>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Maximum 500 characters</p>
            @error('bio') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
        </div>

        <!-- Timezone -->
        <div>
            <label for="timezone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Timezone <span class="text-red-500">*</span>
            </label>
            <select id="timezone" wire:model="timezone"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                @foreach($timezones as $tz)
                    <option value="{{ $tz }}">{{ $tz }}</option>
                @endforeach
            </select>
            @error('timezone') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
        </div>

        <!-- Locale -->
        <div>
            <label for="locale" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Language <span class="text-red-500">*</span>
            </label>
            <select id="locale" wire:model="locale"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                @foreach($locales as $code => $name)
                    <option value="{{ $code }}">{{ $name }}</option>
                @endforeach
            </select>
            @error('locale') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
        </div>

        <!-- Submit Button -->
        <div class="flex items-center justify-end pt-4 border-t border-gray-200 dark:border-gray-700">
            <button type="submit"
                class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Save Changes
            </button>
        </div>
    </form>
</div>
