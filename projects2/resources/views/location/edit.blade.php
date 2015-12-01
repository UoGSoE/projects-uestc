@extends('layout')

@section('content')

    <h2>
        Edit Location
        <a action-href="{!! action('LocationController@destroy', $location->id) !!}" data-confirm="Really delete this location? This will remove ALL projects, courses and users with this location!" class="btn btn-danger pull-right data-confirm">
            Delete
        </a>
    </h2>
    <form method="POST" action="{!! action('LocationController@update', $location->id) !!}">
        <input type="hidden" name="_method" value="PATCH">
        @include('location.partials.location_form')
        <button type="submit" class="btn btn-primary">Update</button>
    </form>

@stop