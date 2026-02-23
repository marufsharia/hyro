<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Profile Settings</h1>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Manage your account settings and preferences</p>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <p class="ml-3 text-sm text-green-800 dark:text-green-200">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if (session()->has('warning'))
        <div class="mb-6 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <p class="ml-3 text-sm text-yellow-800 dark:text-yellow-200">{{ session('warning') }}</p>
            </div>
        </div>
    @endif

    <!-- Tabs -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm mb-6">
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="flex -mb-px overflow-x-auto" aria-label="Tabs">
                <button wire:click="$set('activeTab', 'profile')" 
                    class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors {{ $activeTab === 'profile' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Profile
                    </div>
                </button>
                
                <button wire:click="$set('activeTab', 'avatar')" 
                    class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors {{ $activeTab === 'avatar' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Avatar
                    </div>
                </button>
                
                <button wire:click="$set('activeTab', 'security')" 
                    class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors {{ $activeTab === 'security' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        Security
                    </div>
                </button>
                
                <button wire:click="$set('activeTab', 'two-factor')" 
                    class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors {{ $activeTab === 'two-factor' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        Two-Factor Auth
                    </div>
                </button>
                
                <button wire:click="$set('activeTab', 'danger')" 
                    class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors {{ $activeTab === 'danger' ? 'border-red-500 text-red-600 dark:text-red-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        Danger Zone
                    </div>
                </button>
            </nav>
        </div>
    </div>

    <!-- Tab Content -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
        
        <!-- Profile Tab -->
        @if($activeTab === 'profile')
            <div wire:key="tab-profile">
                @include('hyro::profile.tabs.profile-info')
            </div>
        @endif

        <!-- Avatar Tab -->
        @if($activeTab === 'avatar')
            <div wire:key="tab-avatar">
                @include('hyro::profile.tabs.avatar')
            </div>
        @endif

        <!-- Security Tab -->
        @if($activeTab === 'security')
            <div wire:key="tab-security">
                @include('hyro::profile.tabs.security')
            </div>
        @endif

        <!-- Two-Factor Tab -->
        @if($activeTab === 'two-factor')
            <div wire:key="tab-two-factor">
                @include('hyro::profile.tabs.two-factor')
            </div>
        @endif

        <!-- Danger Zone Tab -->
        @if($activeTab === 'danger')
            <div wire:key="tab-danger">
                @include('hyro::profile.tabs.danger-zone')
            </div>
        @endif

    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('avatarChanged', () => {
            // Reload the page after a delay to ensure database update completes
            // and to show the toast message
            setTimeout(() => {
                window.location.reload();
            }, 1500); // Increased to 1.5 seconds
        });
    });
</script>
@endpush
