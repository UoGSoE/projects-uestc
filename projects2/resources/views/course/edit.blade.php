@extends('layout')

@section('content')
    <div class="container">
        <h2>
            Edit Course {{ $course->code }}
            <a action-href="{!! action('CourseController@destroy', $course->id) !!}" data-confirm="Really delete this course?" class="btn btn-danger pull-right data-confirm">
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
