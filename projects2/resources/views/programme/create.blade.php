@extends('layout')

@section('content')
    <div class="container">
        <h2>
            New Programme
        </h2>

        <form method="POST" action="{!! action('ProgrammeController@store') !!}">

            @include ('programme.partials.programme_form')

            <p></p>
            <button type="submit" class="btn btn-primary">Create</button>

        </form>
    </div>
@stop
