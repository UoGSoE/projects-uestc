@component('mail::message')
# Student Projects

This is an automatic email from the University of Glasgow student projects
system. Please follow the link below to set your password.

<a href="{!! route('password.reset', ['token' => $token->token]) !!}">
{!! route('password.reset'), ['token' => $token->token]) !!}
</a>

Thanks,<br>
{{ config('app.name') }}

@endcomponent
