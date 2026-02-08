@component('mail::message')
# Account Suspended

Hello {{ $user->name }},

Your account has been suspended.

**Suspended by:** {{ $suspender ?? 'System Administrator' }}  
**Reason:** {{ $reason ?? 'No reason provided' }}  
@if($durationDays)
**Duration:** {{ $durationDays }} days  
@else
**Duration:** Indefinite  
@endif

## What This Means

During this suspension period:
- You will not be able to log in to your account
- All your active sessions will be terminated
- Your API tokens have been revoked

@if($durationDays)
Your account will be automatically reactivated after {{ $durationDays }} days.
@else
Your account will remain suspended until manually reactivated by an administrator.
@endif

If you believe this is an error or would like to appeal, please contact support.

Thanks,  
{{ config('app.name') }}
@endcomponent
