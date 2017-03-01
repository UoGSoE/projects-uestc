@extends('layout')

@section('content')
    <div class="container">
        <h2>
            New Discipline
        </h2>

        <form method="POST" action="{!! route('discipline.store') !!}">

            @include ('discipline.partials.discipline_form')

            <p></p>
            <button type="submit" class="btn btn-primary">Create</button>

        </form>
    </div>
@stop
