@extends('layout')

@section('content')
    <div class="container">
        <h2>
            Edit Course {{ $course->code }}
            <a href="{!! route('course.destroy', $course->id) !!}" class="btn btn-danger pull-right">
                Delete
            </a>
        </h2>

        <form method="POST" action="{!! action('CourseController@update', $course->id) !!}">

            @include ('course.partials.course_form')

            <p></p>
            <button type="submit" class="btn btn-primary">Update</button>

        </form>
    </div>
@stop
