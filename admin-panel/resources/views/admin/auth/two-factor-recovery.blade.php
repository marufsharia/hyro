<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ hyro_config('appearance.app_name', config('app.name', 'Hyro')) }} - Recovery Code</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    
    <!-- Dynamic Styles & Favicon -->
    @include('hyro::admin.auth.partials.dynamic-styles')
    
    <!-- Styles -->
        @hyroCss
    @livewireStyles
    @stack('styles')
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="h-full bg-gradient-to-br from-primary-50 via-white to-primary-100 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900">
    <div class="min-h-full flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Logo & Header -->
            <div class="text-center">
                @include('hyro::admin.auth.partials.logo', ['iconType' => 'key'])
                <h2 class="mt-6 text-3xl font-bold text-gray-900 dark:text-white">
                    Use Recovery Code
                </h2>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Enter one of your recovery codes
                </p>
            </div>

            <!-- Recovery Form -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 space-y-6 border border-gray-200 dark:border-gray-700">
                @if ($errors->any())
                    <div class="rounded-lg bg-red-50 dark:bg-red-900/20 p-4 border border-red-200 dark:border-red-800">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-red-800 dark:text-red-200">
                                    @foreach ($errors->all() as $error)
                                        {{ $error }}
                                    @endforeach
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('hyro.2fa.verify') }}" class="space-y-6">
                    @csrf
                    <input type="hidden" name="recovery" value="1">

                    <!-- Code Input -->
                    <div>
                        <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Recovery Code
                        </label>
                        <input 
                            id="code" 
                            name="code" 
                            type="text" 
                            required 
                            autofocus
                            class="appearance-none block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white text-center font-mono @error('code') border-red-500 @enderror"
                            placeholder="xxxxxxxxxxxx-xxxxxxxxxxxx"
                        >
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <button 
                            type="submit"
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all duration-200 transform hover:scale-[1.02]"
                        >
                            Verify Recovery Code
                        </button>
                    </div>

                    <!-- Back to 2FA Link -->
                    <div class="text-center">
                        <a href="{{ route('hyro.2fa.verify') }}" class="text-sm text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300 font-medium">
                            Use authenticator code instead
                        </a>
                    </div>
                </form>

                <!-- Warning Box -->
                <div class="mt-6 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                    <div class="flex">
                        <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700 dark:text-yellow-300">
                                Each recovery code can only be used once. Make sure to generate new codes after using one.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Back to Login -->
            <div class="text-center">
                <a href="{{ route('hyro.login') }}" class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200 font-medium">
                    ← Back to login
                </a>
            </div>

            <!-- Footer -->
            <p class="text-center text-xs text-gray-500 dark:text-gray-400">
                &copy; {{ date('Y') }} {{ hyro_config('appearance.app_name', config('app.name', 'Hyro')) }}. All rights reserved.
            </p>
        </div>
    </div>
     @hyroJs
    @livewireScripts
    @stack('scripts')
</body>
</html>
