<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - @yield('title', 'Home')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <!-- Additional Styles -->
    @stack('styles')
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-50">
        <!-- Navigation -->
        @include('layouts.partials.frontend-nav')

        <!-- Page Content -->
        <main>
            @yield('content')
            {{$slot??''}}
        </main>

        <!-- Footer -->
        @include('layouts.partials.frontend-footer')
    </div>

    @livewireScripts

    <!-- Additional Scripts -->
    @stack('scripts')

    <!-- Toast Notifications -->
    <div x-data="{ show: false, message: '', type: 'info' }"
         @notify.window="show = true; message = $event.detail.message; type = $event.detail.type; setTimeout(() => show = false, 5000)"
         x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed bottom-4 right-4 z-50 max-w-sm"
         style="display: none;">
        <div class="rounded-lg shadow-lg p-4"
             :class="{
                 'bg-blue-500 text-white': type === 'info',
                 'bg-green-500 text-white': type === 'success',
                 'bg-yellow-500 text-white': type === 'warning',
                 'bg-red-500 text-white': type === 'error'
             }">
            <div class="flex items-center justify-between">
                <p x-text="message" class="font-medium"></p>
                <button @click="show = false" class="ml-4 text-white hover:text-gray-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</body>
</html>
