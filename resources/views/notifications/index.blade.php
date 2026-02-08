@extends('hyro::admin.layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <livewire:hyro.notification-center />
    </div>
</div>
@endsection
