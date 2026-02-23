@component('mail::message')
# Account Reactivated

Hello {{ $user->name }},

Good news! Your account has been reactivated.

**Reactivated by:** {{ $unsuspender ?? 'System Administrator' }}  
**Reason:** {{ $reason ?? 'Suspension period ended' }}

## You Can Now

- ✓ Log in to your account
- ✓ Access all your previous privileges
- ✓ Use the system normally

@component('mail::button', ['url' => url('/login'), 'color' => 'success'])
Log In Now
@endcomponent

Welcome back! If you have any questions, please contact support.

Thanks,  
{{ config('app.name') }}
@endcomponent
