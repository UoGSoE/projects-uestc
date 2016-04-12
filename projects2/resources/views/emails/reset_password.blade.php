This is an automatic email from the University of Glasgow student projects
system.  If you did not request a password reset then please ignore this email.

If you follow this link you will be able to pick a new password for the system.

<a href="{!! action('Auth\AuthController@password', ['token' => $token->token]) !!}">
{!! action('Auth\AuthController@password', ['token' => $token->token]) !!}
</a>

