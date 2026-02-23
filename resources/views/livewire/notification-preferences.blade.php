<div class="notification-preferences bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Notification Preferences</h3>
    
    <p class="text-sm text-gray-600 mb-6">
        Choose how you want to receive notifications about your account activity.
    </p>

    <form wire:submit.prevent="updatePreferences" class="space-y-4">
        {{-- Email Notifications --}}
        <div class="flex items-center justify-between py-3 border-b border-gray-200">
            <div class="flex-1">
                <label class="text-sm font-medium text-gray-900">Email Notifications</label>
                <p class="text-xs text-gray-500 mt-1">Receive notifications via email</p>
            </div>
            <div>
                <button type="button" 
                        wire:click="toggle('email')"
                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 {{ $preferences['email'] ?? true ? 'bg-blue-600' : 'bg-gray-200' }}"
                        role="switch" 
                        aria-checked="{{ $preferences['email'] ?? true ? 'true' : 'false' }}">
                    <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $preferences['email'] ?? true ? 'translate-x-5' : 'translate-x-0' }}"></span>
                </button>
            </div>
        </div>

        {{-- Database Notifications --}}
        <div class="flex items-center justify-between py-3 border-b border-gray-200">
            <div class="flex-1">
                <label class="text-sm font-medium text-gray-900">In-App Notifications</label>
                <p class="text-xs text-gray-500 mt-1">Show notifications in the notification center</p>
            </div>
            <div>
                <button type="button" 
                        wire:click="toggle('database')"
                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 {{ $preferences['database'] ?? true ? 'bg-blue-600' : 'bg-gray-200' }}"
                        role="switch" 
                        aria-checked="{{ $preferences['database'] ?? true ? 'true' : 'false' }}">
                    <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $preferences['database'] ?? true ? 'translate-x-5' : 'translate-x-0' }}"></span>
                </button>
            </div>
        </div>

        {{-- Push Notifications --}}
        <div class="flex items-center justify-between py-3 border-b border-gray-200">
            <div class="flex-1">
                <label class="text-sm font-medium text-gray-900">Push Notifications</label>
                <p class="text-xs text-gray-500 mt-1">Receive real-time push notifications</p>
            </div>
            <div>
                <button type="button" 
                        wire:click="toggle('push')"
                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 {{ $preferences['push'] ?? false ? 'bg-blue-600' : 'bg-gray-200' }}"
                        role="switch" 
                        aria-checked="{{ $preferences['push'] ?? false ? 'true' : 'false' }}">
                    <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $preferences['push'] ?? false ? 'translate-x-5' : 'translate-x-0' }}"></span>
                </button>
            </div>
        </div>

        {{-- SMS Notifications --}}
        <div class="flex items-center justify-between py-3">
            <div class="flex-1">
                <label class="text-sm font-medium text-gray-900">SMS Notifications</label>
                <p class="text-xs text-gray-500 mt-1">Receive notifications via SMS (if configured)</p>
            </div>
            <div>
                <button type="button" 
                        wire:click="toggle('sms')"
                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 {{ $preferences['sms'] ?? false ? 'bg-blue-600' : 'bg-gray-200' }}"
                        role="switch" 
                        aria-checked="{{ $preferences['sms'] ?? false ? 'true' : 'false' }}">
                    <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $preferences['sms'] ?? false ? 'translate-x-5' : 'translate-x-0' }}"></span>
                </button>
            </div>
        </div>
    </form>

    {{-- Flash Messages --}}
    @if(session()->has('success'))
        <div x-data="{ show: true }" 
             x-show="show" 
             x-init="setTimeout(() => show = false, 3000)"
             class="mt-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif
</div>
