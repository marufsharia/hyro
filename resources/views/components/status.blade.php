<?php
{{--
    Component: hyro-status
    Usage: <x-hyro-status ability="users.create" />
--}}

@props([
    'ability' => null,
    'role' => null,
    'privilege' => null,
    'user' => null,
    'showIcon' => true,
    'showText' => true,
    'size' => 'sm',
])

@php
    // Get user
    $currentUser = $user ?? auth()->user();

    // Determine what to check
    $checkType = null;
    $checkValue = null;

    if ($ability) {
        $checkType = 'ability';
        $checkValue = $ability;
    } elseif ($role) {
        $checkType = 'role';
        $checkValue = $role;
    } elseif ($privilege) {
        $checkType = 'privilege';
        $checkValue = $privilege;
    }

    // Check authorization
    $authorized = false;
    $icon = '⛔';
    $color = 'text-red-600';
    $bgColor = 'bg-red-50';
    $text = 'Unauthorized';

    if ($currentUser && $checkType) {
        try {
            if ($checkType === 'ability') {
                $authorized = $currentUser->hasPrivilege($checkValue);
            } elseif ($checkType === 'role') {
                $authorized = $currentUser->hasRole($checkValue);
            } elseif ($checkType === 'privilege') {
                $authorized = $currentUser->hasPrivilege($checkValue);
            }

            if ($authorized) {
                $icon = '✅';
                $color = 'text-green-600';
                $bgColor = 'bg-green-50';
                $text = 'Authorized';
            }
        } catch (\Exception $e) {
            $text = 'Error';
            $icon = '⚠️';
            $color = 'text-yellow-600';
            $bgColor = 'bg-yellow-50';
        }
    } elseif (!$currentUser) {
        $text = 'Not logged in';
    }

    // Size classes
    $sizeClasses = [
        'xs' => 'text-xs px-2 py-1',
        'sm' => 'text-sm px-3 py-1.5',
        'md' => 'text-base px-4 py-2',
        'lg' => 'text-lg px-5 py-3',
    ];

    $selectedSize = $sizeClasses[$size] ?? $sizeClasses['sm'];
@endphp

<span class="inline-flex items-center rounded-md {{ $selectedSize }} {{ $bgColor }} {{ $color }} font-medium">
    @if($showIcon)
        <span class="mr-2">{{ $icon }}</span>
    @endif
    @if($showText)
        <span>{{ $text }}</span>
        @if($checkValue)
            <span class="ml-1 opacity-75">({{ $checkValue }})</span>
        @endif
    @endif
</span>
