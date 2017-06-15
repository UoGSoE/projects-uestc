@component('mail::message')
# Student Projects

This is an automatic email from the University of Glasgow student projects
system. Please follow the link below to set your password.

<a href="{!! action('Auth\AuthController@password', ['token' => $token->token]) !!}">
{!! action('Auth\AuthController@password', ['token' => $token->token]) !!}
</a>

Thanks,<br>
{{ config('app.name') }}

@endcomponent
