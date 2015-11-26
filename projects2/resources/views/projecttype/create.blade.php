@extends('layout')

@section('content')

    <h2>Create a new Project Type</h2>
    <form method="POST" action="{!! action('ProjectTypeController@store') !!}">
        @include('projecttype.partials.type_form')
        <button type="submit" class="btn btn-primary">Create</button>
    </form>
@stop