@extends('layout')

@section('content')

    <h2>
        Edit Location
        <form method="POST" action="{!! action('LocationController@destroy', $location->id) !!}" class="pull-right">
            {{ csrf_field() }}
            <input type="hidden" name="_method" value="DELETE">
            <button type="submit" value="Delete" class="btn btn-danger" data-toggle="modal" data-target="#confirm-model">Delete</button>
        </form>
    </h2>
    <form method="POST" action="{!! action('LocationController@update', $location->id) !!}">
        <input type="hidden" name="_method" value="PATCH">
        @include('location.partials.location_form')
        <button type="submit" class="btn btn-primary">Update</button>
    </form>

@stop