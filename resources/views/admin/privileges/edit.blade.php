{{-- resources/views/admin/privileges/edit.blade.php --}}
@extends('hyro::admin.layouts.app')

@section('header', 'Edit Privilege')

@section('content')
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">
                    Edit Privilege: {{ $privilege->name }}
                </h2>
                <p class="text-gray-600 dark:text-gray-400 mt-1">
                    Update privilege details and configuration
                </p>
            </div>
            <a href="{{ route('hyro.admin.privileges.index') }}"
               class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg transition-colors inline-flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Privileges
            </a>
        </div>
    </div>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-800">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Privilege Details</h3>
                <p class="text-gray-600 dark:text-gray-400 text-sm mt-1">Update the privilege information</p>
            </div>

            <form action="{{ route('hyro.admin.privileges.update', $privilege) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="p-6 space-y-6">
                    @if($errors->any())
                        <div class="mb-6 p-4 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-red-600 dark:text-red-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="text-red-800 dark:text-red-300">Please fix the errors below</span>
                            </div>
                            <ul class="mt-2 list-disc list-inside text-sm text-red-700 dark:text-red-300">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if(session('success'))
                        <div class="mb-6 p-4 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-green-600 dark:text-green-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span class="text-green-800 dark:text-green-300">{{ session('success') }}</span>
                            </div>
                        </div>
                    @endif

                    <!-- Name Field -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Privilege Name *
                        </label>
                        <input type="text"
                               name="name"
                               id="name"
                               value="{{ old('name', $privilege->name) }}"
                               required
                               class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                               placeholder="e.g., Manage Users">
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            A descriptive name for the privilege
                        </p>
                    </div>

                    <!-- Slug Field -->
                    <div>
                        <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Privilege Slug *
                        </label>
                        <input type="text"
                               name="slug"
                               id="slug"
                               value="{{ old('slug', $privilege->slug) }}"
                               required
                               class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                               placeholder="e.g., manage-users">
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            A unique slug (use lowercase with hyphens). This will be used in code.
                        </p>
                    </div>

                    <!-- Category Field -->
                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Category
                        </label>
                        <input type="text"
                               name="category"
                               id="category"
                               value="{{ old('category', $privilege->category) }}"
                               class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                               placeholder="e.g., User Management">
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Optional category to group related privileges
                        </p>
                    </div>

                    <!-- Description Field -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Description
                        </label>
                        <textarea name="description"
                                  id="description"
                                  rows="4"
                                  class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                  placeholder="Describe what this privilege allows...">{{ old('description', $privilege->description) }}</textarea>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Optional description of what this privilege grants
                        </p>
                    </div>

                    <!-- Role Assignment Info -->
                    <div class="border rounded-lg p-4 bg-gray-50 dark:bg-gray-800/50">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-blue-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-white">Role Assignments</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                    This privilege is currently assigned to
                                    <span class="font-semibold">{{ $privilege->roles_count ?? 0 }} roles</span>.
                                    To manage role assignments, edit individual roles.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex items-center justify-between pt-6 border-t border-gray-200 dark:border-gray-800">
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            * Required fields
                        </div>
                        <div class="flex gap-3">
                            <a href="{{ route('hyro.admin.privileges.index') }}"
                               class="px-5 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                Cancel
                            </a>
                            <button type="submit"
                                    class="px-5 py-2.5 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition-colors inline-flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Update Privilege
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Danger Zone -->
        @if(!$privilege->isCoreAdminPrivilege())
            <div class="mt-8 bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-red-300 dark:border-red-800">
                <div class="px-6 py-4 border-b border-red-300 dark:border-red-800 bg-red-50 dark:bg-red-900/20">
                    <h3 class="text-lg font-semibold text-red-800 dark:text-red-300">Danger Zone</h3>
                </div>
                <div class="p-6">
                    <div class="flex items-start">
                        <div class="w-10 h-10 rounded-full mr-4 flex items-center justify-center bg-red-100 dark:bg-red-900/30">
                            <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.698-.833-2.464 0L4.272 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-lg font-semibold text-red-800 dark:text-red-300 mb-2">Delete Privilege</h4>
                            <p class="text-sm text-red-700 dark:text-red-400 mb-4">
                                Once you delete a privilege, there is no going back. This will remove the privilege from all roles that have it assigned.
                            </p>
                            <form action="{{ route('hyro.admin.privileges.destroy', $privilege) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this privilege? This action cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors inline-flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Delete Privilege
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="mt-8 bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-yellow-300 dark:border-yellow-800">
                <div class="px-6 py-4 border-b border-yellow-300 dark:border-yellow-800 bg-yellow-50 dark:bg-yellow-900/20">
                    <h3 class="text-lg font-semibold text-yellow-800 dark:text-yellow-300">Protected Privilege</h3>
                </div>
                <div class="p-6">
                    <div class="flex items-start">
                        <div class="w-10 h-10 rounded-full mr-4 flex items-center justify-center bg-yellow-100 dark:bg-yellow-900/30">
                            <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-lg font-semibold text-yellow-800 dark:text-yellow-300 mb-2">Core Admin Privilege</h4>
                            <p class="text-sm text-yellow-700 dark:text-yellow-400">
                                This is a core admin privilege and cannot be deleted. Core privileges are required for the system to function properly.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Tips Card -->
        <div class="mt-8 bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 p-6">
            <div class="flex items-start">
                <div class="w-10 h-10 rounded-full mr-4 flex items-center justify-center bg-blue-100 dark:bg-blue-900/30">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Editing Tips</h4>
                    <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-blue-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Changing the slug may require updating your application code</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-blue-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Use categories to organize related privileges</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-blue-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Core admin privileges cannot be deleted for system integrity</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-blue-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Provide clear descriptions to help administrators understand privileges</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Auto-generate slug from name if empty
        document.getElementById('name').addEventListener('input', function(e) {
            const nameInput = e.target;
            const slugInput = document.getElementById('slug');

            // Only auto-generate if slug is empty or matches the original
            const originalSlug = "{{ $privilege->slug }}";
            if (!slugInput.value || slugInput.value === originalSlug) {
                const slug = nameInput.value
                    .toLowerCase()
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '');
                slugInput.value = slug;
            }
        });
    </script>
@endpush
