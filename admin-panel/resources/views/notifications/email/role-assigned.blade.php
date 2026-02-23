@component('mail::message')
# New Role Assigned

Hello {{ $user->name }}!

You have been assigned a new role: **{{ $roleName }}**

**Assigned by:** {{ $assigner ?? 'System Administrator' }}  
@if($reason)
**Reason:** {{ $reason }}
@endif

## Privileges

This role grants you the following privileges:

@if(!empty($privileges))
@foreach($privileges as $privilege)
- {{ $privilege }}
@endforeach
@else
No specific privileges assigned yet.
@endif

@component('mail::button', ['url' => url('/admin/profile/roles')])
View Your Roles
@endcomponent

If you have any questions, please contact your administrator.

Thanks,  
{{ config('app.name') }}
@endcomponent
