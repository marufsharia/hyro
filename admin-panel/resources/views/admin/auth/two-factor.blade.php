<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ hyro_config('appearance.app_name', config('app.name', 'Hyro')) }} - Two-Factor Authentication</title>
    
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
                @include('hyro::admin.auth.partials.logo', ['iconType' => 'shield'])
                <h2 class="mt-6 text-3xl font-bold text-gray-900 dark:text-white">
                    Two-Factor Authentication
                </h2>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Enter the 6-digit code from your authenticator app
                </p>
            </div>

            <!-- 2FA Form -->
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

                    <!-- Code Input -->
                    <div>
                        <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Authentication Code
                        </label>
                        <input 
                            id="code" 
                            name="code" 
                            type="text" 
                            maxlength="6"
                            pattern="[0-9]{6}"
                            inputmode="numeric"
                            autocomplete="one-time-code"
                            required 
                            autofocus
                            class="appearance-none block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white text-center text-2xl tracking-widest font-mono @error('code') border-red-500 @enderror"
                            placeholder="000000"
                        >
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <button 
                            type="submit"
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all duration-200 transform hover:scale-[1.02]"
                        >
                            Verify Code
                        </button>
                    </div>

                    <!-- Recovery Code Link -->
                    <div class="text-center">
                        <a href="{{ route('hyro.2fa.recovery') }}" class="text-sm text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300 font-medium">
                            Use a recovery code instead
                        </a>
                    </div>
                </form>

                <!-- Info Box -->
                <div class="mt-6 bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-800 rounded-lg p-4">
                    <div class="flex">
                        <svg class="w-5 h-5 text-primary-600 dark:text-primary-400 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <div class="ml-3">
                            <p class="text-sm text-primary-700 dark:text-primary-300">
                                Open your authenticator app (Google Authenticator, Authy, etc.) and enter the 6-digit code.
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

    <script>
        // Auto-submit when 6 digits are entered
        document.getElementById('code').addEventListener('input', function(e) {
            if (e.target.value.length === 6) {
                e.target.form.submit();
            }
        });
    </script>
     @hyroJs
    @livewireScripts
    @stack('scripts')
</body>
</html>
