@extends('layout')

@section('content')

    <h2>
        Courses
        <a href="{!! route('course.create') !!}" class="btn btn-default">New Course</a>
    </h2>
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>Code</th>
                <th>Title</th>
                <th>Students</th>
                <th>Projects</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($courses as $course)
                <tr>
                    <td><a href="{!! action('CourseController@show', $course->id) !!}">{{ $course->code }}</a></td>
                    <td>{{ $course->title }}</td>
                    <td>{{ $course->students->count() }}</td>
                    <td>{{ $course->projects->count() }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@stop
