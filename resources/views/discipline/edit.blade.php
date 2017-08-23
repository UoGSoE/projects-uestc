@extends('layout')

@section('content')
    <div class="container">
        <h2>
            Edit Discipline
        </h2>

        <form method="POST" action="{!! route('discipline.update', $discipline->id) !!}">

            @include ('discipline.partials.discipline_form')

            <p></p>
            <button type="submit" class="btn btn-primary">Update</button>

        </form>
    </div>
@stop
