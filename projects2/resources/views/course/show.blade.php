@extends('layout')

@section('content')
    <div class="container">
        <h2>
            Course {{ $course->code }}
            <a href="{!! action('CourseController@edit', $course->id) !!}" class="btn btn-default">Edit</a>
        </h2>
        <dl>
            <dt>Title</dt>
            <dd>{{ $course->title }}</dd>
        </dl>
    </div>
@stop
