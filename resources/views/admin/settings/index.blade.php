@extends('hyro::admin.layouts.app')

@section('title', 'Settings')

@section('content')
<div x-data="{ activeTab: 'general' }" class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Settings</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage your Hyro system configuration</p>
        </div>
        
        <!-- Cache Actions -->
        <div class="flex items-center space-x-2">
            <form action="{{ route('hyro.admin.settings.cache.all') }}" method="POST" class="inline">
                @csrf
                <button type="submit" 
                    class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Clear All Caches
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Left Sidebar - Navigation -->
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <nav class="space-y-1 p-2">
                    <button @click="activeTab = 'general'" 
                        :class="activeTab === 'general' ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'"
                        class="w-full flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors duration-200">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        General
                    </button>

                    <button @click="activeTab = 'admin'" 
                        :class="activeTab === 'admin' ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'"
                        class="w-full flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors duration-200">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Admin Panel
                    </button>

                    <button @click="activeTab = 'crud'" 
                        :class="activeTab === 'crud' ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'"
                        class="w-full flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors duration-200">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        CRUD Generator
                    </button>

                    <button @click="activeTab = 'rbac'" 
                        :class="activeTab === 'rbac' ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'"
                        class="w-full flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors duration-200">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        RBAC
                    </button>

                    <button @click="activeTab = 'plugins'" 
                        :class="activeTab === 'plugins' ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'"
                        class="w-full flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors duration-200">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        Plugins
                    </button>

                    <button @click="activeTab = 'system'" 
                        :class="activeTab === 'system' ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'"
                        class="w-full flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors duration-200">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                        </svg>
                        System
                    </button>
                </nav>
            </div>
        </div>

        <!-- Right Panel - Content -->
        <div class="lg:col-span-3">
            <!-- General Settings -->
            <div x-show="activeTab === 'general'" x-transition>
                @include('hyro::admin.settings.sections.general', ['settings' => $settings['general']])
            </div>

            <!-- Admin Settings -->
            <div x-show="activeTab === 'admin'" x-transition style="display: none;">
                @include('hyro::admin.settings.sections.admin', ['settings' => $settings['admin']])
            </div>

            <!-- CRUD Settings -->
            <div x-show="activeTab === 'crud'" x-transition style="display: none;">
                @include('hyro::admin.settings.sections.crud', ['settings' => $settings['crud']])
            </div>

            <!-- RBAC Settings -->
            <div x-show="activeTab === 'rbac'" x-transition style="display: none;">
                @include('hyro::admin.settings.sections.rbac', ['settings' => $settings['rbac']])
            </div>

            <!-- Plugin Settings -->
            <div x-show="activeTab === 'plugins'" x-transition style="display: none;">
                @include('hyro::admin.settings.sections.plugins', ['settings' => $settings['plugins']])
            </div>

            <!-- System Settings -->
            <div x-show="activeTab === 'system'" x-transition style="display: none;">
                @include('hyro::admin.settings.sections.system', ['settings' => $settings['system']])
            </div>
        </div>
    </div>
</div>
@endsection
