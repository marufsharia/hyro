<?php
{{--
    Component: hyro-privilege
    Usage: <x-hyro-privilege name="users.create" [chip="true"] [icon="check"]>
        Content for users with privilege
    </x-hyro-privilege>
--}}

@props([
    'name' => null,
    'names' => null,
    'all' => false,
    'chip' => false,
    'icon' => null,
    'showIcon' => true,
    'users' => null,
    'hide' => false,
    'tooltip' => true,
])

@php
    // Get users
    $currentUser = $user ?? auth()->user();

    // Determine if users has required privilege(s)
    $hasPrivilege = false;

    if ($name) {
        $hasPrivilege = $currentUser && method_exists($currentUser, 'hasPrivilege') && $currentUser->hasPrivilege($name);
    } elseif ($names) {
        $namesArray = is_array($names) ? $names : explode(',', str_replace(' ', '', $names));
        if ($all) {
            $hasPrivilege = $currentUser && method_exists($currentUser, 'hasPrivileges') && $currentUser->hasPrivileges($namesArray);
        } else {
            $hasPrivilege = $currentUser && method_exists($currentUser, 'hasAnyPrivilege') && $currentUser->hasAnyPrivilege($namesArray);
        }
    }

    // Icons
    $icons = [
        'check' => '<svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>',
        'lock' => '<svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>',
        'shield' => '<svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>',
        'key' => '<svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 8a6 6 0 01-7.743 5.743L10 14l-1 1-1 1H6v2H2v-4l4.257-4.257A6 6 0 1118 8zm-6-4a1 1 0 100 2 2 2 0 012 2 1 1 0 102 0 4 4 0 00-4-4z" clip-rule="evenodd"/></svg>',
    ];

    $selectedIcon = $icons[$icon] ?? $icons['check'];

    // Privilege display name
    $displayName = $name ?? (is_array($names) ? implode(', ', $names) : $names);
    $formattedName = str_replace('.', ' ∙ ', $displayName);

    // Tooltip attributes
    $tooltipAttrs = $tooltip ? 'data-tooltip="Requires: ' . e($displayName) . '"' : '';
@endphp

@if($hasPrivilege)
    @if($chip)
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 border border-green-200" {!! $tooltipAttrs !!}>
            @if($showIcon && $icon)
                {!! $selectedIcon !!}
            @endif
            {{ $formattedName }}
        </span>
    @else
        {{ $slot }}
    @endif
@elseif(!$hide && config('app.debug') && $chip)
    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-400 border border-dashed border-gray-300 opacity-50" {!! $tooltipAttrs !!}>
        @if($showIcon && $icon)
            {!! str_replace('currentColor', '#9CA3AF', $selectedIcon) !!}
        @endif
        {{ $formattedName }} (missing)
    </span>
@endif
