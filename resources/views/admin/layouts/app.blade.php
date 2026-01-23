<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Hyro'))</title>

    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">
    @hyroAssets
    <!-- Hyro CSS -->
    @hyroCss

    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-100">
<div class="min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <!-- Logo -->
                    <div class="flex-shrink-0 flex items-center">
                        <a href="{{ route('hyro.admin.dashboard') }}" class="text-xl font-bold text-indigo-600">
                            Hyro Admin
                        </a>
                    </div>

                    <!-- Navigation Links -->
                    <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                        <a href="{{ route('hyro.admin.dashboard') }}"
                           class="{{ request()->routeIs('hyro.admin.dashboard') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Dashboard
                        </a>

                        @can('view-roles')
                            <a href="{{ route('hyro.admin.roles.index') }}"
                               class="{{ request()->routeIs('hyro.admin.roles.*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Roles
                            </a>
                        @endcan

                        @can('view-privileges')
                            <a href="{{ route('hyro.admin.privileges.index') }}"
                               class="{{ request()->routeIs('hyro.admin.privileges.*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Privileges
                            </a>
                        @endcan
                    </div>
                </div>

                <!-- Settings Dropdown -->
                <div class="hidden sm:flex sm:items-center sm:ml-6">
                    <div class="ml-3 relative">
                        <div class="flex items-center">
                                <span class="text-sm text-gray-700 mr-4">
{{--                                    {{  Auth::user()->name }}--}}
                                </span>
                            <a href="{{ url('/') }}"
                               class="text-sm text-gray-500 hover:text-gray-700">
                                Back to Site
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Page Heading -->
    <header class="bg-white shadow">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    @yield('header')
                </h2>
                @hasSection('actions')
                    <div class="flex space-x-2">
                        @yield('actions')
                    </div>
                @endif
            </div>
        </div>
    </header>

    <!-- Page Content -->
    <main>
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <!-- Flash Messages -->
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <ul class="mt-1 list-disc list-inside text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Main Content -->
            <main class="hyro-container" style="padding: 2rem 0;">
                @yield('content')
            </main>
        </div>
    </main>
</div>
<!-- Toast Container -->
<div class="hyro-toast-container"></div>

<!-- Hyro JS -->
@hyroJs
<!-- Scripts -->
@stack('scripts')
</body>
</html>
