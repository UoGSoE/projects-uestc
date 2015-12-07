@extends('layout')

@section('content')
	<div class="jumbotron">
        <h1>
        	Student Projects
        </h1>
    </div>
    <div class="container text-center">
    	@if(count($errors) > 0)
        	<div class="alert alert-danger">
        		{{ $errors }}
        	</div>
    	@endif
		<form class="form-inline" role="form" method="POST" action="{{ url("/auth/login") }}" id="loginform">
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
            <br /><p />
            <p id="resetbox">
                If you don't have a password yet, then <a href="#" id="resetlink">click here to generate one</a>.
            </p>
		</form>
        <div id="resetform" style="display: none">
            <form class="form-inline" role="form" method="POST" action="{{ url("/auth/login") }}" id="loginform">
              <input type="hidden" name="_token" value="{{ csrf_token() }}" >
              <div class="form-group">
                <label class="sr-only" for="username">Username</label>
                <input type="text" class="form-control" id="username" name="username" placeholder="Username">
              </div>
              <button type="submit" class="btn btn-primary">Send password link</button>
            </form>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            $("#resetlink").click(function(e) {
                e.preventDefault();
                $("#loginform").fadeOut(500, function() {
                    $("#resetform").fadeIn();
                });
            });
        });
    </script>
@stop
