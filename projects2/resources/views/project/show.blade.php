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
        <dd>
            @can('view_users')
                <a href="{!! action('UserController@show', $project->owner->id) !!}">
                    {{ $project->owner->fullName() }}
                </a>
            @else
                    {{ $project->owner->fullName() }}
            @endcan
        </dd>
        <dt>Maximum Students</dt>
        <dd>{{ $project->maximum_students }}</dd>
        @if ($project->programmes()->count() > 0)
            <dt>Programmes</dt>
            <dd>
                <ul class="list-inline">
                    @foreach ($project->programmes as $programme)
                        <li>{{ $programme->title }}</li>
                    @endforeach
                </ul>
            </dd>
        @endif
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
    <h3>
        Students who applied for this project
    </h3>
    <p class="help-block">
        Please be careful accepting or un-accepting students. This triggers an automatic email to the student
        and can cause some confusion for them if you have made a mistake.
        @cannot('allocate_students')
            <br /><b>Note:</b> You can only accept students who have made this project their first choice.
        @endcannot
    </p>
    <form method="POST" action="{!! action('ProjectController@acceptStudents', $project->id) !!}">
    {{ csrf_field() }}
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
                    <td>
                        {{ $student->matric() }}
                        @if ($student->pivot->accepted)
                            <span class="glyphicon glyphicon-ok" title="Accepted">
                        @endif
                    </td>
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
    @if ($project->acceptedStudents->count() < $project->maximum_students or Auth::user()->can('allocate_students'))
        <button type="submit" class="btn btn-primary pull-right">Allocate</button>
    @endif
    </form>
@stop