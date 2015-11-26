@extends('layout')

@section('content')
    <div class="container">
        <h2>
            New Course
        </h2>

        <form method="POST" action="{!! action('CourseController@store') !!}">

            @include ('course.partials.course_form')

            <p></p>
            <button type="submit" class="btn btn-primary">Create</button>

        </form>
    </div>
@stop
