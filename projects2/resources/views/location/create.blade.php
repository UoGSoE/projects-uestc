@extends('layout')

@section('content')

    <h2>Create a new Location</h2>
    <form method="POST" action="{!! action('LocationController@store') !!}">
        @include('location.partials.location_form')
        <button type="submit" class="btn btn-primary">Create</button>
    </form>
@stop