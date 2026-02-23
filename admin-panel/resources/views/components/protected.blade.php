<?php
{{--
    Component: hyro-protected
    Usage: <x-hyro-protected ability="users.create" [role="admin"] [privilege="users.*"]>
        Content to protect
    </x-hyro-protected>
--}}

@props([
    'ability' => null,
    'role' => null,
    'roles' => null,
    'privilege' => null,
    'privileges' => null,
    'suspended' => false,
    'fallback' => null,
    'hide' => false,
])

@php
    // Determine if content should be shown
    $shouldShow = false;
    $user = auth()->user();

    // Check suspended condition
    if ($suspended === true) {
        $shouldShow = $user && method_exists($user, 'isSuspended') && $user->isSuspended();
    } elseif ($suspended === false) {
        $shouldShow = !$user || !method_exists($user, 'isSuspended') || !$user->isSuspended();
    } else {
        // Check authorization conditions
        if ($ability) {
            $shouldShow = $user && method_exists($user, 'hasPrivilege') && $user->hasPrivilege($ability);
        } elseif ($role) {
            $shouldShow = $user && method_exists($user, 'hasRole') && $user->hasRole($role);
        } elseif ($roles) {
            $rolesArray = is_array($roles) ? $roles : explode(',', str_replace(' ', '', $roles));
            $shouldShow = $user && method_exists($user, 'hasAnyRole') && $user->hasAnyRole($rolesArray);
        } elseif ($privilege) {
            $shouldShow = $user && method_exists($user, 'hasPrivilege') && $user->hasPrivilege($privilege);
        } elseif ($privileges) {
            $privilegesArray = is_array($privileges) ? $privileges : explode(',', str_replace(' ', '', $privileges));
            $shouldShow = $user && method_exists($user, 'hasAnyPrivilege') && $user->hasAnyPrivilege($privilegesArray);
        }
    }

    // Default to showing if no conditions specified
    if (!$ability && !$role && !$roles && !$privilege && !$privileges && $suspended === null) {
        $shouldShow = true;
    }
@endphp

@if($shouldShow)
    {{ $slot }}
@elseif($fallback)
    {{ $fallback }}
@elseif(!$hide)
    {{-- Optional: Show a placeholder or tooltip for debugging --}}
    @if(config('app.debug'))
        <div class="hyro-protected-placeholder border border-dashed border-gray-300 p-2 text-gray-500 text-sm">
            @if($ability)
                Requires: {{ $ability }}
            @elseif($role)
                Requires role: {{ $role }}
            @elseif($roles)
                Requires any role: {{ is_array($roles) ? implode(', ', $roles) : $roles }}
            @elseif($privilege)
                Requires privilege: {{ $privilege }}
            @elseif($privileges)
                Requires any privilege: {{ is_array($privileges) ? implode(', ', $privileges) : $privileges }}
            @endif
        </div>
    @endif
@endif
