{{-- Hyro Asset Manager Component --}}
{{-- This component renders all registered styles and scripts --}}

@php
    use Marufsharia\Hyro\Core\Support\Assets\AssetManager;
@endphp

{{-- Render all registered stylesheets --}}
{!! AssetManager::renderStyles() !!}

{{-- Render all registered scripts --}}
{!! AssetManager::renderScripts() !!}
