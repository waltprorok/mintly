@component('mail::message')

# New Support Request

**User:** {{ auth()->user()?->email }}

**Subject:** {{ $data['subject'] }}

@component('mail::panel')

    {{ $data['message'] }}

@endcomponent

<small>Sent from Mintly Support Form</small>

@endcomponent
