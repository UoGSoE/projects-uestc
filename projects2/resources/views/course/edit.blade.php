@extends('layout')

@section('content')
    <div class="container">
        <h2>
            Edit Course {{ $course->code }}
            <form method="POST" action="{!! route('course.destroy', $course->id) !!}" class="pull-right">
                {!! csrf_field() !!}
                <input type="hidden" name="_method" value="DELETE">
                <button class="btn btn-danger pull-right">
                    Delete
                </button>
            </form>
        </h2>

        <form method="POST" action="{!! action('CourseController@update', $course->id) !!}">

            @include ('course.partials.course_form')

            <p></p>
            <button type="submit" class="btn btn-primary">Update</button>

        </form>
    </div>
@stop
