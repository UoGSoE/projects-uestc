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
            <dt>Projects</dt>
            <dd>
                <ul class="list-inline">
                    @foreach ($course->projects as $project)
                        <li>
                            <a href="{!! action('ProjectController@show', $project->id) !!}">
                                {{ $project->title }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </dd>
        </dl>
        <h3>
            Students
            <a href="{!! action('CourseController@editStudents', $course->id) !!}" class="btn btn-default">Import</a>
            <a action-href="{!! action('CourseController@removeStudents', $course->id) !!}" data-confirm="Really remove all students? This will delete them from the system along with all their choices etc" class="btn btn-danger pull-right data-confirm">
                Remove All Students
            </a>
        </h3>
        @if ($course->students->count() == 0)
            None
        @else
            @include('report.partials.student_list', ['students' => $course->students])
        @endif
    </div>
@stop
