{{-- Hyro Alert System Scripts --}}
@once
    {{-- Include CSS directly in head via inline style or direct link --}}
    <link rel="stylesheet" href="{{ asset('vendor/hyro/css/hyro-alert.css') }}">
    
    @push('scripts')
        <script src="{{ asset('vendor/hyro/js/hyro-alert.js') }}"></script>
    @endpush
@endonce

{{-- Handle Laravel session flash messages --}}
@if(session()->has('success') || session()->has('error') || session()->has('warning') || session()->has('info'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if(session()->has('success'))
                hyroAlert.toast('{{ session('success') }}', 'success');
            @endif

            @if(session()->has('error'))
                hyroAlert.toast('{{ session('error') }}', 'error');
            @endif

            @if(session()->has('warning'))
                hyroAlert.toast('{{ session('warning') }}', 'warning');
            @endif

            @if(session()->has('info'))
                hyroAlert.toast('{{ session('info') }}', 'info');
            @endif
        });
    </script>
@endif

{{-- Livewire event listeners --}}
@if(isset($livewire))
    <script>
        document.addEventListener('livewire:init', () => {
            // Success event
            Livewire.on('alert:success', (data) => {
                if (typeof data === 'string') {
                    hyroAlert.toast(data, 'success');
                } else if (data[0]) {
                    hyroAlert.success(data[0].title || 'Success', data[0].message || '');
                }
            });

            // Error event
            Livewire.on('alert:error', (data) => {
                if (typeof data === 'string') {
                    hyroAlert.toast(data, 'error');
                } else if (data[0]) {
                    hyroAlert.error(data[0].title || 'Error', data[0].message || '');
                }
            });

            // Warning event
            Livewire.on('alert:warning', (data) => {
                if (typeof data === 'string') {
                    hyroAlert.toast(data, 'warning');
                } else if (data[0]) {
                    hyroAlert.warning(data[0].title || 'Warning', data[0].message || '');
                }
            });

            // Info event
            Livewire.on('alert:info', (data) => {
                if (typeof data === 'string') {
                    hyroAlert.toast(data, 'info');
                } else if (data[0]) {
                    hyroAlert.info(data[0].title || 'Info', data[0].message || '');
                }
            });

            // Toast event
            Livewire.on('alert:toast', (data) => {
                if (data[0]) {
                    hyroAlert.toast(
                        data[0].message || data[0],
                        data[0].type || 'info',
                        data[0].duration || 3000
                    );
                }
            });
        });
    </script>
@endif
