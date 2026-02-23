@extends('hyro::admin.layouts.app')

@section('title', 'Plugin Manager')

@section('content')
    @livewire('hyro.plugin-manager')
@endsection

@push('scripts')
<script>
// Livewire notification handler
document.addEventListener('livewire:init', () => {
    Livewire.on('notify', (event) => {
        const data = Array.isArray(event) ? event[0] : event;
        const type = data.type || 'info';
        const message = data.message || 'Notification';
        
        // Create notification element
        const colors = {
            success: 'bg-green-500',
            error: 'bg-red-500',
            warning: 'bg-yellow-500',
            info: 'bg-blue-500'
        };
        
        const notification = document.createElement('div');
        notification.className = `fixed top-20 right-4 ${colors[type]} text-white px-6 py-4 rounded-lg shadow-2xl z-40 transform transition-all duration-300`;
        notification.innerHTML = `
            <div class="flex items-center space-x-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    ${type === 'success' ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>' : ''}
                    ${type === 'error' ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>' : ''}
                    ${type === 'warning' ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>' : ''}
                    ${type === 'info' ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>' : ''}
                </svg>
                <span class="font-medium">${message}</span>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.opacity = '0';
            setTimeout(() => notification.remove(), 300);
        }, 5000);
    });
});
</script>
@endpush

@push('styles')
<style>
    [x-cloak] { display: none !important; }
    
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    /* Shimmer animation for loading states */
    @keyframes shimmer {
        0% {
            background-position: -1000px 0;
        }
        100% {
            background-position: 1000px 0;
        }
    }
    
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .animate-fade-in {
        animation: fadeIn 0.3s ease-out;
    }
    
    .animate-pulse {
        animation: shimmer 2s infinite linear;
        background: linear-gradient(
            to right,
            #f3f4f6 0%,
            #e5e7eb 20%,
            #f3f4f6 40%,
            #f3f4f6 100%
        );
        background-size: 1000px 100%;
    }
    
    .dark .animate-pulse {
        background: linear-gradient(
            to right,
            #374151 0%,
            #4b5563 20%,
            #374151 40%,
            #374151 100%
        );
        background-size: 1000px 100%;
    }
</style>
@endpush
