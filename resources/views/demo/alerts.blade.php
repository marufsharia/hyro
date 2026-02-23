@extends('hyro::admin.layouts.app')

@section('title', 'Alert System Demo')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Hyro Alert System Demo</h1>
        <p class="text-gray-600 dark:text-gray-400">Test all alert types and features</p>
    </div>

    <!-- Basic Alerts -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Basic Alerts</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <button onclick="hyroAlert.success('Success!', 'Your operation completed successfully.')"
                class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition">
                Success Alert
            </button>
            
            <button onclick="hyroAlert.error('Error!', 'Something went wrong. Please try again.')"
                class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition">
                Error Alert
            </button>
            
            <button onclick="hyroAlert.warning('Warning!', 'This action requires your attention.')"
                class="px-6 py-3 bg-orange-600 hover:bg-orange-700 text-white rounded-lg font-medium transition">
                Warning Alert
            </button>
            
            <button onclick="hyroAlert.info('Information', 'Here is some useful information for you.')"
                class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition">
                Info Alert
            </button>
        </div>
    </div>

    <!-- Toast Notifications -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Toast Notifications</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <button onclick="hyroAlert.toast('Success toast notification!', 'success')"
                class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition">
                Success Toast
            </button>
            
            <button onclick="hyroAlert.toast('Error toast notification!', 'error')"
                class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition">
                Error Toast
            </button>
            
            <button onclick="hyroAlert.toast('Warning toast notification!', 'warning')"
                class="px-6 py-3 bg-orange-600 hover:bg-orange-700 text-white rounded-lg font-medium transition">
                Warning Toast
            </button>
            
            <button onclick="hyroAlert.toast('Info toast notification!', 'info')"
                class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition">
                Info Toast
            </button>
        </div>
    </div>

    <!-- Interactive Dialogs -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Interactive Dialogs</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <button onclick="testConfirm()"
                class="px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium transition">
                Confirmation Dialog
            </button>
            
            <button onclick="testInput()"
                class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition">
                Input Dialog
            </button>
            
            <button onclick="testChoice()"
                class="px-6 py-3 bg-pink-600 hover:bg-pink-700 text-white rounded-lg font-medium transition">
                Choice Dialog
            </button>
        </div>
    </div>

    <!-- Advanced Examples -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Advanced Examples</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <button onclick="testDeleteConfirmation()"
                class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition">
                Delete Confirmation
            </button>
            
            <button onclick="testMultiStep()"
                class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition">
                Multi-Step Process
            </button>
            
            <button onclick="testAutoClose()"
                class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition">
                Auto-Close Alert
            </button>
            
            <button onclick="testLongMessage()"
                class="px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-medium transition">
                Long Message
            </button>
        </div>
    </div>

    <!-- Code Examples -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Code Examples</h2>
        <div class="space-y-4">
            <div>
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Basic Alert</h3>
                <pre class="bg-gray-100 dark:bg-gray-900 p-4 rounded-lg overflow-x-auto"><code class="text-sm">hyroAlert.success('Success!', 'Your operation completed successfully.');</code></pre>
            </div>
            
            <div>
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Confirmation</h3>
                <pre class="bg-gray-100 dark:bg-gray-900 p-4 rounded-lg overflow-x-auto"><code class="text-sm">const confirmed = await hyroAlert.confirm('Delete?', 'Are you sure?');
if (confirmed) {
    // User confirmed
}</code></pre>
            </div>
            
            <div>
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Input Dialog</h3>
                <pre class="bg-gray-100 dark:bg-gray-900 p-4 rounded-lg overflow-x-auto"><code class="text-sm">const name = await hyroAlert.input('Enter Name', {
    inputPlaceholder: 'John Doe',
    required: true
});</code></pre>
            </div>
        </div>
    </div>
</div>

<script>
    // Confirmation Dialog
    async function testConfirm() {
        const confirmed = await hyroAlert.confirm(
            'Are you sure?',
            'This action requires your confirmation.',
            {
                confirmButtonText: 'Yes, proceed',
                cancelButtonText: 'Cancel'
            }
        );
        
        if (confirmed) {
            hyroAlert.toast('You confirmed the action!', 'success');
        } else {
            hyroAlert.toast('Action cancelled', 'info');
        }
    }

    // Input Dialog
    async function testInput() {
        try {
            const name = await hyroAlert.input('Enter Your Name', {
                inputPlaceholder: 'John Doe',
                inputValue: '',
                required: true,
                message: 'Please enter your full name'
            });
            
            hyroAlert.success('Hello!', `Nice to meet you, ${name}!`);
        } catch (error) {
            hyroAlert.toast('Input cancelled', 'info');
        }
    }

    // Choice Dialog
    async function testChoice() {
        try {
            const role = await hyroAlert.choice('Select Your Role', [
                { value: 'user', label: 'User' },
                { value: 'editor', label: 'Editor' },
                { value: 'admin', label: 'Administrator' }
            ], {
                message: 'Choose your preferred role',
                required: true
            });
            
            hyroAlert.success('Role Selected', `You selected: ${role}`);
        } catch (error) {
            hyroAlert.toast('Selection cancelled', 'info');
        }
    }

    // Delete Confirmation
    async function testDeleteConfirmation() {
        const confirmed = await hyroAlert.confirm(
            'Delete Item?',
            'This action cannot be undone. Are you sure you want to delete this item?',
            {
                confirmButtonText: 'Yes, delete it',
                cancelButtonText: 'No, keep it'
            }
        );
        
        if (confirmed) {
            hyroAlert.toast('Item deleted successfully', 'success');
        }
    }

    // Multi-Step Process
    async function testMultiStep() {
        try {
            // Step 1: Get name
            const name = await hyroAlert.input('Step 1: Enter Name', {
                required: true
            });
            
            // Step 2: Get email
            const email = await hyroAlert.input('Step 2: Enter Email', {
                inputType: 'email',
                required: true
            });
            
            // Step 3: Confirm
            const confirmed = await hyroAlert.confirm(
                'Confirm Details',
                `Name: ${name}\nEmail: ${email}\n\nIs this correct?`
            );
            
            if (confirmed) {
                hyroAlert.success('Success!', 'Your information has been saved.');
            }
        } catch (error) {
            hyroAlert.toast('Process cancelled', 'info');
        }
    }

    // Auto-Close Alert
    function testAutoClose() {
        hyroAlert.success('Auto-Close', 'This alert will close in 3 seconds', {
            timer: 3000
        });
    }

    // Long Message
    function testLongMessage() {
        hyroAlert.info(
            'Important Information',
            'This is a longer message that demonstrates how the alert system handles multiple lines of text. The alert will automatically adjust its height to accommodate the content while maintaining a clean and readable layout. You can include as much information as needed, and the alert will remain centered and properly formatted.'
        );
    }
</script>
@endsection
