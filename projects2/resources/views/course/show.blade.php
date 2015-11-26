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
            <dt>Location</dt>
            <dd>{{ $course->location_id ? $course->location->title : 'Anywhere' }}</dd>
        </dl>
        <h3>
            Students
            <a href="{!! action('CourseController@editStudents', $course->id) !!}" class="btn btn-default">Import</a>
        </h3>
        @if ($course->students->count() == 0)
            None
        @else
            @foreach ($course->students()->orderBy('surname')->get() as $student)
                <li>
                    <a href="{!! action('UserController@show', $student->id) !!}">{{ $student->fullName() }}</a>
                    ({{ $student->matric() }})
                </li>
            @endforeach
        @endif
    </div>
@stop
