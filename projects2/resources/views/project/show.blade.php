@extends('layout')

@section('content')

    <h2>
        Project Details
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
                    <li>{{ $course->code }} {{ $course->title }}</li>
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
                        <input type="checkbox" value="1" @if ($student->pivot->accepted) checked @endif>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@stop