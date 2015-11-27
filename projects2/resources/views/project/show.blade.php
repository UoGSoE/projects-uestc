@extends('layout')

@section('content')

    <h2>
        Project Details
        @can('edit_this_project', $project)
            <a href="{!! action('ProjectController@edit', $project->id) !!}" class="btn btn-default">
                Edit
            </a>
        @endcan
    </h2>
    <dl>
        <dt>Title</dt>
        <dd>{{ $project->title }}</dd>
        <dt>Description</dt>
        <dd>{{ $project->description }}</dd>
        <dt>Prerequisits</dt>
        <dd>{{ $project->prereq or 'None' }}</dd>
        <dt>Active?</dt>
        <dd>{{ $project->is_active ? 'Yes' : 'No' }}</dd>
        <dt>Run By</dt>
        <dd>{{ $project->owner->fullName() }}</dd>
        <dt>Location</dt>
        <dd>{{ $project->location_id ? $project->location->title : 'Anywhere' }}</dd>
        <dt>Maximum Students</dt>
        <dd>{{ $project->maximum_students }}</dd>
        <dt>Programmes</dt>
        <dd>
            <ul class="list-inline">
                @foreach ($project->programmes as $programme)
                    <li>{{ $programme->title }}</li>
                @endforeach
            </ul>
        </dd>
        <dt>Courses</dt>
        <dd>
            <ul class="list-inline">
                @foreach ($project->courses as $course)
                    @can('edit_courses')
                        <li>
                            <a href="{!! action('CourseController@show', $course->id) !!}">
                                {{ $course->code }} {{ $course->title }}</li>
                            </a>
                        </li>
                    @else
                        <li>{{ $course->code }} {{ $course->title }}</li>
                    @endcan
                @endforeach
            </ul>
        </dd>
    </dl>
    <h3>Students</h3>
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Matric</th>
                <th>Name</th>
                <th>Choice</th>
                <th>Accept?</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($project->students as $student)
                <tr>
                    <td>{{ $student->matric() }}</td>
                    <td>{{ $student->fullName() }}</td>
                    <td>{{ $choices[$student->pivot->choice] }}</td>
                    <td>
                        @can('allocate_students')
                            <input type="hidden" value="0" name="accepted[{{ $student->id }}]">
                            <input type="checkbox" value="1" name="accepted[{{ $student->id }}]" @if ($student->pivot->accepted) checked @endif>
                        @else
                            @if ($student->pivot->choice == 1)
                                <input type="hidden" value="0" name="accepted[{{ $student->id }}]">
                                <input type="checkbox" value="1" name="accepted[{{ $student->id }}]" @if ($student->pivot->accepted) checked @endif>
                            @endif
                        @endcan
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@stop