<div>
    @auth
    <div class="notification-bell relative" x-data="{ open: false }" @click.away="open = false">
        {{-- Bell Icon --}}
        <button @click="open = !open" 
                class="relative p-2 text-gray-600 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded-lg">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            
            @if($unreadCount > 0)
                <span class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full">
                    {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                </span>
            @endif
        </button>

        {{-- Dropdown --}}
        <div x-show="open" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 z-50"
             style="display: none;">
            
            {{-- Header --}}
            <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900">
                    Notifications
                    @if($unreadCount > 0)
                        <span class="ml-1 text-xs text-gray-500">({{ $unreadCount }})</span>
                    @endif
                </h3>
                
                @if($unreadCount > 0)
                    <button wire:click="markAllAsRead" 
                            class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                        Mark all read
                    </button>
                @endif
            </div>

            {{-- Notifications List --}}
            <div class="max-h-96 overflow-y-auto">
                @if($recentNotifications->isEmpty())
                    <div class="px-4 py-8 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                        </svg>
                        <p class="mt-2 text-sm text-gray-500">No new notifications</p>
                    </div>
                @else
                    @foreach($recentNotifications as $notification)
                        <div wire:click="markAsRead('{{ $notification->id }}')" 
                             class="px-4 py-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0">
                            <p class="text-sm text-gray-900 font-medium">
                                {{ $notification->data['message'] ?? 'Notification' }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ $notification->created_at->diffForHumans() }}
                            </p>
                        </div>
                    @endforeach
                @endif
            </div>

            {{-- Footer --}}
            <div class="px-4 py-3 border-t border-gray-200 text-center">
                <a href="{{ route('notifications.index') }}" 
                   class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                    View all notifications
                </a>
            </div>
        </div>
    </div>
    @endauth
</div>