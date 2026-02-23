@props(['notification'])

@php
    $data = $notification->data;
    $type = $data['type'] ?? 'info';
    $severity = $data['severity'] ?? 'low';
    
    $iconMap = [
        'user_suspended' => 'exclamation-triangle',
        'user_unsuspended' => 'check-circle',
        'role_assigned' => 'shield-check',
        'role_revoked' => 'shield-exclamation',
        'privilege_granted' => 'key',
        'privilege_revoked' => 'key',
        'admin_user_suspended' => 'user-slash',
    ];
    
    $colorMap = [
        'high' => 'red',
        'medium' => 'yellow',
        'low' => 'blue',
        'info' => 'blue',
    ];
    
    $icon = $iconMap[$type] ?? 'bell';
    $color = $colorMap[$severity] ?? 'gray';
@endphp

<div class="notification-item flex items-start p-4 border-b border-gray-200 hover:bg-gray-50 transition {{ $notification->read_at ? 'opacity-60' : '' }}"
     data-notification-id="{{ $notification->id }}">
    
    {{-- Icon --}}
    <div class="flex-shrink-0 mr-3">
        <div class="w-10 h-10 rounded-full bg-{{ $color }}-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-{{ $color }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                @if($icon === 'exclamation-triangle')
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                @elseif($icon === 'check-circle')
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                @elseif($icon === 'shield-check')
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                @elseif($icon === 'key')
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                @else
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                @endif
            </svg>
        </div>
    </div>

    {{-- Content --}}
    <div class="flex-1 min-w-0">
        <p class="text-sm font-medium text-gray-900">
            {{ $data['message'] ?? 'Notification' }}
        </p>
        
        @if(isset($data['role_name']))
            <p class="text-sm text-gray-600 mt-1">
                Role: <span class="font-semibold">{{ $data['role_name'] }}</span>
            </p>
        @endif
        
        @if(isset($data['privilege_name']))
            <p class="text-sm text-gray-600 mt-1">
                Privilege: <span class="font-semibold">{{ $data['privilege_name'] }}</span>
            </p>
        @endif
        
        @if(isset($data['reason']) && $data['reason'])
            <p class="text-sm text-gray-600 mt-1">
                Reason: {{ $data['reason'] }}
            </p>
        @endif
        
        <p class="text-xs text-gray-500 mt-2">
            {{ $notification->created_at->diffForHumans() }}
        </p>
    </div>

    {{-- Actions --}}
    <div class="flex-shrink-0 ml-3 flex items-center space-x-2">
        @if(!$notification->read_at)
            <button wire:click="markAsRead('{{ $notification->id }}')" 
                    class="text-blue-600 hover:text-blue-800 text-xs font-medium">
                Mark Read
            </button>
        @endif
        
        <button wire:click="deleteNotification('{{ $notification->id }}')" 
                class="text-red-600 hover:text-red-800 text-xs font-medium">
            Delete
        </button>
    </div>
</div>
