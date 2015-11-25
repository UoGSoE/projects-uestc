@extends('layout')

@section('content')
	<div class="jumbotron">
        <h1>
        	Student Projects
        </h1>
        <p>
            To continue you need to log in with your University username &amp; password.
        </p>
    </div>
    <div class="container text-center">
    	@if(count($errors) > 0)
        	<div class="alert alert-danger">
        		{{ $errors }}
        	</div>
    	@endif
		<form class="form-inline" role="form" method="POST" action="{{ url("/auth/login") }}">
          <input type="hidden" name="_token" value="{{ csrf_token() }}" >
		  <div class="form-group">
		    <label class="sr-only" for="username">Username</label>
		    <input type="text" class="form-control" id="username" name="username" placeholder="Username">
		  </div>
		  <div class="form-group">
		    <label class="sr-only" for="password">Password</label>
		    <input type="password" class="form-control" id="password" name="password" placeholder="Password">
		  </div>
		  <button type="submit" class="btn btn-primary">Sign in</button>
		</form>
    </div>
@stop
