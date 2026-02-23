@component('mail::message')
# Role Revoked

Hello {{ $user->name }}!

Your role **{{ $roleName }}** has been revoked.

**Revoked by:** {{ $revoker ?? 'System Administrator' }}  
@if($reason)
**Reason:** {{ $reason }}
@endif

## What This Means

You no longer have access to the privileges associated with this role.

@component('mail::button', ['url' => url('/admin/profile/roles')])
View Your Roles
@endcomponent

If you believe this is an error, please contact your administrator.

Thanks,  
{{ config('app.name') }}
@endcomponent
