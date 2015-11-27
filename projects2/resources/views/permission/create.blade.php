@extends('layout')

@section('content')
    <div class="container">
        <h2>
            New Permission
        </h2>

        <form method="POST" action="{!! action('PermissionController@store') !!}">

            @include ('permission.partials.permission_form')

            <p></p>
            <button type="submit" class="btn btn-primary">Create</button>

        </form>
    </div>
@stop
