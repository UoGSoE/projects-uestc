@extends('layout')

@section('content')
    <div class="container">
        <h2>
            New Role
        </h2>

        <form method="POST" action="{!! action('RoleController@store') !!}">

            @include ('role.partials.role_form')

            <p></p>
            <button type="submit" class="btn btn-primary">Create</button>

        </form>
    </div>
@stop
