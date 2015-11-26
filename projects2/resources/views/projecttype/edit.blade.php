@extends('layout')

@section('content')

    <h2>
        Edit Project Type
        <form method="POST" action="{!! action('ProjectTypeController@destroy', $type->id) !!}" class="pull-right">
            {{ csrf_field() }}
            <input type="hidden" name="_method" value="DELETE">
            <button type="submit" value="Delete" class="btn btn-danger" data-toggle="modal" data-target="#confirm-model">Delete</button>
        </form>
    </h2>
    <form method="POST" action="{!! action('ProjectTypeController@update', $type->id) !!}">
        <input type="hidden" name="_method" value="PATCH">
        @include('projecttype.partials.type_form')
        <button type="submit" class="btn btn-primary">Update</button>
    </form>

@stop