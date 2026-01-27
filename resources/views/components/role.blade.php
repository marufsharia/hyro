<?php
{{--
    Component: hyro-role
    Usage: <x-hyro-role name="admin" [badge="true"] [color="red"]>
        Content for admins
    </x-hyro-role>
--}}

@props([
    'name' => null,
    'names' => null,
    'all' => false,
    'badge' => false,
    'color' => 'blue',
    'size' => 'sm',
    'users' => null,
    'hide' => false,
])

@php
    // Get users
    $currentUser = $user ?? auth()->user();

    // Determine if users has required role(s)
    $hasRole = false;

    if ($name) {
        $hasRole = $currentUser && method_exists($currentUser, 'hasRole') && $currentUser->hasRole($name);
    } elseif ($names) {
        $namesArray = is_array($names) ? $names : explode(',', str_replace(' ', '', $names));
        if ($all) {
            $hasRole = $currentUser && method_exists($currentUser, 'hasRoles') && $currentUser->hasRoles($namesArray);
        } else {
            $hasRole = $currentUser && method_exists($currentUser, 'hasAnyRole') && $currentUser->hasAnyRole($namesArray);
        }
    }

    // Color classes
    $colorClasses = [
        'red' => 'bg-red-100 text-red-800 border-red-200',
        'blue' => 'bg-blue-100 text-blue-800 border-blue-200',
        'green' => 'bg-green-100 text-green-800 border-green-200',
        'yellow' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
        'purple' => 'bg-purple-100 text-purple-800 border-purple-200',
        'pink' => 'bg-pink-100 text-pink-800 border-pink-200',
        'indigo' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
        'gray' => 'bg-gray-100 text-gray-800 border-gray-200',
    ];

    $selectedColor = $colorClasses[$color] ?? $colorClasses['blue'];

    // Size classes
    $sizeClasses = [
        'xs' => 'px-2 py-0.5 text-xs',
        'sm' => 'px-2.5 py-1 text-sm',
        'md' => 'px-3 py-1.5 text-base',
        'lg' => 'px-4 py-2 text-lg',
    ];

    $selectedSize = $sizeClasses[$size] ?? $sizeClasses['sm'];

    // Role display name
    $displayName = $name ?? (is_array($names) ? implode(', ', $names) : $names);
@endphp

@if($hasRole)
    @if($badge)
        <span class="inline-flex items-center rounded-full border font-medium {{ $selectedSize }} {{ $selectedColor }}">
            {{ $displayName }}
        </span>
    @else
        {{ $slot }}
    @endif
@elseif(!$hide && config('app.debug') && $badge)
    <span class="inline-flex items-center rounded-full border border-dashed border-gray-300 px-2.5 py-1 text-sm font-medium text-gray-400 opacity-50">
        {{ $displayName }} (missing)
    </span>
@endif
