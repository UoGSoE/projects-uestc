@extends('layout')

@section('content')
    <div class="container">
        <h2>
            Edit Permission
            <form method="POST" action="{!! action('PermissionController@destroy', $permission->id) !!}" class="pull-right">
                {{ csrf_field() }}
                <input type="hidden" name="_method" value="DELETE">
                <button type="submit" value="Delete" class="btn btn-danger" data-toggle="modal" data-target="#confirm-model">Delete</button>
            </form>
        </h2>

        <form method="POST" action="{!! action('PermissionController@update', $permission->id) !!}">
            <input type="hidden" name="_method" value="PATCH">

            @include ('permission.partials.permission_form')

            <p></p>
            <button type="submit" class="btn btn-primary">Update</button>

        </form>
    </div>
@stop
