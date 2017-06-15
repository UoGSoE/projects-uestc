@extends('layout')

@section('content')
    <div class="container">
        <h2>
            Update your password
        </h2>

        <form method="POST" action="{!! action('Auth\AuthController@resetPassword', $token) !!}">
            {!! csrf_field() !!}
            <div class="form-group">
                <label for="inputPassword1">New Password</label>
                <input type="password" id="inputPassword1" name="password1" value="" class="form-control" required>
                <p class="help-block">
                    Your password must be <em>at least</em> 12 characters long.
                </p>
            </div>
            <div class="form-group">
                <label for="inputPassword2">Confirm Password</label>
                <input type="password" id="inputPassword2" name="password2" value="" class="form-control" required>
            </div>
            <p></p>
            <button type="submit" class="btn btn-primary">Update and log in</button>

        </form>
    </div>
@stop
