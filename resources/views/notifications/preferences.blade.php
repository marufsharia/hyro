@extends('hyro::admin.layouts.app')

@section('title', 'Notification Preferences')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Notification Preferences</h1>
            <p class="mt-2 text-sm text-gray-600">
                Manage how you receive notifications about your account activity.
            </p>
        </div>

        <livewire:hyro.notification-preferences />
    </div>
</div>
@endsection
