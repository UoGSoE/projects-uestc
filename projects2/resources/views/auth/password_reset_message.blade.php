@extends('layout')

@section('content')
<h2>Password reset link sent</h2>
<p>A password reset link has been emailed to {{ $user->email }}.  Please check your email shortly.</p>
@stop
