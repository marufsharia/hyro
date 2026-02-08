<div class="notification-center">
    {{-- Header --}}
    <div class="bg-white border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-900">
                Notifications
                @if($unreadCount > 0)
                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        {{ $unreadCount }}
                    </span>
                @endif
            </h2>

            <div class="flex items-center space-x-2">
                @if($unreadCount > 0)
                    <button wire:click="markAllAsRead" 
                            class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                        Mark All Read
                    </button>
                @endif
                
                <button wire:click="deleteAllRead" 
                        class="text-sm text-red-600 hover:text-red-800 font-medium">
                    Clear Read
                </button>
            </div>
        </div>

        {{-- Filters --}}
        <div class="mt-4 flex space-x-4">
            <button wire:click="setFilter('all')" 
                    class="px-3 py-1 text-sm font-medium rounded-md {{ $filter === 'all' ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:bg-gray-100' }}">
                All
            </button>
            <button wire:click="setFilter('unread')" 
                    class="px-3 py-1 text-sm font-medium rounded-md {{ $filter === 'unread' ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:bg-gray-100' }}">
                Unread ({{ $unreadCount }})
            </button>
            <button wire:click="setFilter('read')" 
                    class="px-3 py-1 text-sm font-medium rounded-md {{ $filter === 'read' ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:bg-gray-100' }}">
                Read
            </button>
        </div>
    </div>

    {{-- Notifications List --}}
    <div class="bg-white">
        @if($notifications->isEmpty())
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No notifications</h3>
                <p class="mt-1 text-sm text-gray-500">
                    @if($filter === 'unread')
                        You're all caught up!
                    @elseif($filter === 'read')
                        No read notifications
                    @else
                        You don't have any notifications yet
                    @endif
                </p>
            </div>
        @else
            @foreach($notifications as $notification)
                @include('hyro::notifications.database.notification-item', ['notification' => $notification])
            @endforeach

            {{-- Pagination --}}
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>

    {{-- Flash Messages --}}
    @if(session()->has('success'))
        <div x-data="{ show: true }" 
             x-show="show" 
             x-init="setTimeout(() => show = false, 3000)"
             class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg">
            {{ session('success') }}
        </div>
    @endif
</div>
