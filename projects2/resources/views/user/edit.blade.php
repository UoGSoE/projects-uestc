@extends('layout')

@section('content')
    <div class="container">
        <h2>
            Edit <a href="{!! action('UserController@show', $user->id) !!}">{{ $user->fullName() }}</a>
            <form method="POST" action="{!! action('UserController@destroy', $user->id) !!}" class="pull-right">
                {{ csrf_field() }}
                <input type="hidden" name="_method" value="DELETE">
                <button type="submit" value="Delete" class="btn btn-danger" data-toggle="modal" data-target="#confirm-model">Delete</button>
            </form>
        </h2>

        <form method="POST" action="{!! action('UserController@update', $user->id) !!}">

            @include ('user.partials.user_form')

            <p></p>
            <button type="submit" class="btn btn-primary">Update</button>

        </form>
    </div>
@stop
