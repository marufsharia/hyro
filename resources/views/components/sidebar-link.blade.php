
@props(['route', 'label', 'icon', 'params' => []])

<a href="{{ route($route, $params) }}"
   class="flex items-center px-3 py-2 rounded-md text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700 {{ request()->routeIs($route.'*') ? 'bg-gray-200 dark:bg-gray-700 font-semibold' : '' }}">
    <svg class="h-5 w-5 mr-3 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        {{-- you can replace this with dynamic icon path based on $icon --}}
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M12 5l7 7-7 7"/>
    </svg>
    {{ $label }}
</a>
