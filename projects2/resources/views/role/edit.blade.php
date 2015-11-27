@extends('layout')

@section('content')
    <div class="container">
        <h2>
            Edit Role
        </h2>

        <form method="POST" action="{!! action('RoleController@update', $role->id) !!}">

            <input type="hidden" name="_method" value="PATCH">

            @include ('role.partials.role_form')

            <p></p>
            <button type="submit" class="btn btn-primary">Update</button>

        </form>
    </div>
@stop
