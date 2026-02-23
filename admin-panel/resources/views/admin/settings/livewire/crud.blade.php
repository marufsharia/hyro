<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">CRUD Generator Settings</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Configure CRUD generation behavior</p>
    </div>

    <div class="p-6 space-y-6">
        <!-- Generate API -->
        <div>
            <label class="flex items-center cursor-pointer">
                <input type="checkbox" wire:model="generate_api" class="sr-only peer">
                <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                <span class="ms-3 text-sm font-medium text-gray-700 dark:text-gray-300">Generate API Controllers</span>
            </label>
            <p class="mt-1 ml-14 text-xs text-gray-500 dark:text-gray-400">Automatically generate API controllers and routes when creating CRUD</p>
        </div>

        <!-- Soft Delete -->
        <div>
            <label class="flex items-center cursor-pointer">
                <input type="checkbox" wire:model="soft_delete" class="sr-only peer">
                <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                <span class="ms-3 text-sm font-medium text-gray-700 dark:text-gray-300">Enable Soft Deletes</span>
            </label>
            <p class="mt-1 ml-14 text-xs text-gray-500 dark:text-gray-400">Add SoftDeletes trait to generated models</p>
        </div>

        <!-- Auto Permission -->
        <div>
            <label class="flex items-center cursor-pointer">
                <input type="checkbox" wire:model="auto_permission" class="sr-only peer">
                <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                <span class="ms-3 text-sm font-medium text-gray-700 dark:text-gray-300">Auto-Generate Permissions</span>
            </label>
            <p class="mt-1 ml-14 text-xs text-gray-500 dark:text-gray-400">Automatically create permissions (view, create, update, delete) for generated CRUD</p>
        </div>

        <!-- Info Box -->
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-blue-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="text-sm text-blue-800 dark:text-blue-300">
                    <p class="font-medium mb-1">How CRUD Settings Work</p>
                    <ul class="list-disc list-inside space-y-1 text-xs">
                        <li>Generate API: Creates API controllers and routes alongside web CRUD</li>
                        <li>Soft Deletes: Models use soft deletes instead of permanent deletion</li>
                        <li>Auto Permission: Creates resource.view, resource.create, resource.update, resource.delete permissions</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
            <button wire:click="saveCrud" 
                class="inline-flex items-center px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Save Changes
            </button>
        </div>
    </div>
</div>
