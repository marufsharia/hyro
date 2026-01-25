<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Hyro'))</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <!-- Fonts & Tailwind -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    @hyroCss
    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200">

<div class="flex h-screen overflow-hidden">

    <!-- Sidebar -->
    <aside class="w-64 bg-white dark:bg-gray-800 shadow-md flex flex-col">
        <div class="h-16 flex items-center justify-center border-b border-gray-200 dark:border-gray-700">
            <a href="{{ route('hyro.admin.dashboard') }}" class="text-xl font-bold text-indigo-600 dark:text-indigo-400">Hyro Admin</a>
        </div>
        <nav class="flex-1 overflow-y-auto py-6 px-2 space-y-2">
            <a href="{{ route('hyro.admin.dashboard') }}" class="flex items-center px-3 py-2 rounded-md text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700 {{ request()->routeIs('hyro.admin.dashboard') ? 'bg-gray-200 dark:bg-gray-700 font-semibold' : '' }}">
                <svg class="h-5 w-5 mr-3 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7m-16 0v8a2 2 0 002 2h3m10-10l2 2m-2-2v8a2 2 0 01-2 2h-3m0 0v-8m0 0l-7-7-7 7"/>
                </svg>
                Dashboard
            </a>

            <a href="{{ route('hyro.admin.roles.index') }}" class="flex items-center px-3 py-2 rounded-md text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700 {{ request()->routeIs('hyro.admin.roles.*') ? 'bg-gray-200 dark:bg-gray-700 font-semibold' : '' }}">
                <svg class="h-5 w-5 mr-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Roles
            </a>

            <a href="{{ route('hyro.admin.privileges.index') }}" class="flex items-center px-3 py-2 rounded-md text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700 {{ request()->routeIs('hyro.admin.privileges.*') ? 'bg-gray-200 dark:bg-gray-700 font-semibold' : '' }}">
                <svg class="h-5 w-5 mr-3 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0-3.866-3.582-7-8-7s-8 3.134-8 7a8 8 0 0016 0zm0 0v2a4 4 0 11-8 0v-2"/>
                </svg>
                Privileges
            </a>

            <a href="{{ route('hyro.admin.users.roles.edit', auth()->user()) }}" class="flex items-center px-3 py-2 rounded-md text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700 {{ request()->routeIs('hyro.admin.users.roles.*') ? 'bg-gray-200 dark:bg-gray-700 font-semibold' : '' }}">
                <svg class="h-5 w-5 mr-3 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A5 5 0 0112 15a5 5 0 016.879 2.804M12 11a5 5 0 100-10 5 5 0 000 10z"/>
                </svg>
                Users
            </a>
        </nav>

        <div class="px-4 py-4 border-t border-gray-200 dark:border-gray-700">
            <button id="theme-toggle" class="w-full px-3 py-2 bg-gray-200 dark:bg-gray-700 rounded-md text-gray-800 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600">Toggle Dark/Light</button>
        </div>
    </aside>

    <!-- Main content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Topbar -->
        <header class="h-16 bg-white dark:bg-gray-800 shadow flex items-center justify-between px-6">
            <div class="flex items-center space-x-4">
                <button class="md:hidden text-gray-600 dark:text-gray-300 focus:outline-none" id="sidebar-toggle">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <h1 class="text-xl font-semibold text-gray-800 dark:text-gray-200">@yield('header', 'Dashboard')</h1>
            </div>

            <div class="flex items-center space-x-4">
                <div class="relative">
                    <button id="profile-menu-button" class="flex items-center space-x-2 focus:outline-none">
                        <img class="h-8 w-8 rounded-full object-cover" src="https://i.pravatar.cc/300" alt="Profile">
                        <span>{{ auth()->user()->name }}</span>
                        <svg class="w-4 h-4 text-gray-500 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div class="hidden absolute right-0 mt-2 w-48 bg-white dark:bg-gray-700 shadow-lg rounded-md py-1 z-50" id="profile-menu">
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">Profile</a>
                        <form method="POST" action="{{ route('hyro.logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">Logout</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="flex-1 overflow-y-auto p-6">
            <!-- Flash Messages -->
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded" role="alert">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded" role="alert">
                    <ul class="list-disc list-inside text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="bg-white dark:bg-gray-800 shadow-inner py-4 px-6 text-center text-gray-500 dark:text-gray-400 text-sm">
            &copy; {{ date('Y') }} Hyro Admin. All rights reserved.
        </footer>
    </div>
</div>

<!-- Scripts -->
<script>
    // Sidebar toggle
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebar = document.querySelector('aside');
    sidebarToggle?.addEventListener('click', () => {
        sidebar.classList.toggle('-translate-x-full');
    });

    // Profile dropdown
    const profileBtn = document.getElementById('profile-menu-button');
    const profileMenu = document.getElementById('profile-menu');
    profileBtn?.addEventListener('click', () => {
        profileMenu.classList.toggle('hidden');
    });

    // Dark/Light toggle
    const themeToggle = document.getElementById('theme-toggle');
    themeToggle?.addEventListener('click', () => {
        document.documentElement.classList.toggle('dark');
    });
</script>
@hyroJs
@stack('scripts')
</body>
</html>
