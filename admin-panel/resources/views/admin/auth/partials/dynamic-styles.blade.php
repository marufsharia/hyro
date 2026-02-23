@php
    // Get appearance settings from database
    $primaryColor = hyro_config('appearance.primary_color', '#3B82F6');
    $appLogo = hyro_config('appearance.app_logo');
    $appFavicon = hyro_config('appearance.app_favicon', '/favicon.ico');
    $appName = hyro_config('appearance.app_name', config('app.name', 'Hyro'));
    
    // Convert hex to RGB for Tailwind
    $hex = ltrim($primaryColor, '#');
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    
    // Generate color shades
    function adjustBrightness($r, $g, $b, $percent) {
        $r = max(0, min(255, $r + ($r * $percent / 100)));
        $g = max(0, min(255, $g + ($g * $percent / 100)));
        $b = max(0, min(255, $b + ($b * $percent / 100)));
        return [$r, $g, $b];
    }
    
    // Generate shades
    [$r50, $g50, $b50] = adjustBrightness($r, $g, $b, 80);
    [$r100, $g100, $b100] = adjustBrightness($r, $g, $b, 60);
    [$r200, $g200, $b200] = adjustBrightness($r, $g, $b, 40);
    [$r300, $g300, $b300] = adjustBrightness($r, $g, $b, 20);
    [$r400, $g400, $b400] = adjustBrightness($r, $g, $b, 10);
    [$r500, $g500, $b500] = [$r, $g, $b]; // Base color
    [$r600, $g600, $b600] = adjustBrightness($r, $g, $b, -10);
    [$r700, $g700, $b700] = adjustBrightness($r, $g, $b, -20);
    [$r800, $g800, $b800] = adjustBrightness($r, $g, $b, -30);
    [$r900, $g900, $b900] = adjustBrightness($r, $g, $b, -40);
@endphp

<!-- Favicon -->
<link rel="icon" type="image/x-icon" href="{{ $appFavicon }}">

<!-- Dynamic Primary Color Styles -->
<style>
    :root {
        --primary-50: {{ round($r50) }} {{ round($g50) }} {{ round($b50) }};
        --primary-100: {{ round($r100) }} {{ round($g100) }} {{ round($b100) }};
        --primary-200: {{ round($r200) }} {{ round($g200) }} {{ round($b200) }};
        --primary-300: {{ round($r300) }} {{ round($g300) }} {{ round($b300) }};
        --primary-400: {{ round($r400) }} {{ round($g400) }} {{ round($b400) }};
        --primary-500: {{ round($r500) }} {{ round($g500) }} {{ round($b500) }};
        --primary-600: {{ round($r600) }} {{ round($g600) }} {{ round($b600) }};
        --primary-700: {{ round($r700) }} {{ round($g700) }} {{ round($b700) }};
        --primary-800: {{ round($r800) }} {{ round($g800) }} {{ round($b800) }};
        --primary-900: {{ round($r900) }} {{ round($g900) }} {{ round($b900) }};
    }
    
    /* Background gradients */
    .bg-gradient-to-br.from-primary-50 {
        background-image: linear-gradient(to bottom right, rgb(var(--primary-50)), white, rgb(var(--primary-100)));
    }
    
    .dark .bg-gradient-to-br.from-primary-50 {
        background-image: linear-gradient(to bottom right, rgb(17 24 39), rgb(31 41 55), rgb(17 24 39));
    }
    
    /* Logo gradient */
    .bg-gradient-to-br.from-primary-500 {
        background-image: linear-gradient(to bottom right, rgb(var(--primary-500)), rgb(var(--primary-700)));
    }
    
    /* Shadow */
    .shadow-primary-500\/50 {
        --tw-shadow-color: rgb(var(--primary-500) / 0.5);
        --tw-shadow: var(--tw-shadow-colored);
    }
    
    /* Text colors */
    .text-primary-600 {
        color: rgb(var(--primary-600));
    }
    
    .text-primary-500 {
        color: rgb(var(--primary-500));
    }
    
    .text-primary-400 {
        color: rgb(var(--primary-400));
    }
    
    .text-primary-300 {
        color: rgb(var(--primary-300));
    }
    
    .text-primary-700 {
        color: rgb(var(--primary-700));
    }
    
    .dark .text-primary-600 {
        color: rgb(var(--primary-400));
    }
    
    .dark .text-primary-500 {
        color: rgb(var(--primary-300));
    }
    
    .dark .text-primary-400 {
        color: rgb(var(--primary-400));
    }
    
    .dark .text-primary-300 {
        color: rgb(var(--primary-300));
    }
    
    /* Hover text colors */
    .hover\:text-primary-500:hover {
        color: rgb(var(--primary-500));
    }
    
    .hover\:text-primary-700:hover {
        color: rgb(var(--primary-700));
    }
    
    .dark .hover\:text-primary-500:hover {
        color: rgb(var(--primary-300));
    }
    
    .dark .hover\:text-primary-300:hover {
        color: rgb(var(--primary-300));
    }
    
    /* Background colors */
    .bg-primary-50 {
        background-color: rgb(var(--primary-50));
    }
    
    .bg-primary-600 {
        background-color: rgb(var(--primary-600));
    }
    
    .dark .bg-primary-900\/20 {
        background-color: rgb(var(--primary-900) / 0.2);
    }
    
    /* Border colors */
    .border-primary-200 {
        border-color: rgb(var(--primary-200));
    }
    
    .border-primary-800 {
        border-color: rgb(var(--primary-800));
    }
    
    .dark .border-primary-200 {
        border-color: rgb(var(--primary-800));
    }
    
    .dark .border-primary-800 {
        border-color: rgb(var(--primary-800));
    }
    
    /* Button gradients */
    .bg-gradient-to-r.from-primary-600 {
        background-image: linear-gradient(to right, rgb(var(--primary-600)), rgb(var(--primary-700)));
    }
    
    .hover\:from-primary-700:hover {
        background-image: linear-gradient(to right, rgb(var(--primary-700)), rgb(var(--primary-800)));
    }
    
    /* Focus ring */
    .focus\:ring-primary-500:focus {
        --tw-ring-color: rgb(var(--primary-500));
    }
    
    .focus\:ring-offset-primary-500:focus {
        --tw-ring-offset-color: rgb(var(--primary-500));
    }
    
    /* Checkbox */
    .text-primary-600[type="checkbox"]:checked {
        background-color: rgb(var(--primary-600));
        border-color: rgb(var(--primary-600));
    }
</style>
