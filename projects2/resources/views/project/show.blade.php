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
        <dd id="is_active">{{ $project->is_active ? 'Yes' : 'No' }}</dd>
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
        <dt>Courses</dt>
        <dd>
            <ul class="list-inline">
                @foreach ($project->courses as $course)
                    <li>
                        @can('edit_courses')
                            <a href="{!! action('CourseController@show', $course->id) !!}">
                                {{ $course->code }} {{ $course->title }}
                            </a>
                        @else
                            {{ $course->code }} {{ $course->title }}
                        @endcan
                    </li>
                @endforeach
            </ul>
        </dd>
        <dt>Discipline</dt>
        <dd>{{ $project->disciplineTitle() }}</dd>
    </dl>
    @if ($project->files()->count() > 0)
        <h3>Attached Files</h3>
        @foreach ($project->files as $file)
            <li>
                <a href="{!! route('projectfile.download', $file->id) !!}">
                    {{ $file->original_filename }}
                </a>
            </li>
        @endforeach
    @endif
    @if ($project->links()->count() > 0)
        <h3>Attached links</h3>
        @foreach ($project->links as $link)
            <li>
                <a href="{{ $link->url }}" target="_blank">
                    {{ $link->url }}
                </a>
            </li>
        @endforeach
    @endif
    <h3>
        Students who applied for this project
    </h3>
    <p class="help-block">
        Please be careful accepting or un-accepting students. This triggers an automatic email to the student
        and can cause some confusion for them if you have made a mistake.
    </p>
    <form method="POST" action="{!! route('project.enrol', $project->id) !!}">
    {{ csrf_field() }}
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Matric</th>
                <th>Name</th>
                <th>Accept?</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($project->students as $student)
                <tr>
                    <td>
                        <a href="{!! route('student.profile_show', $student->id) !!}">
                            {{ $student->matric() }}
                        </a>
                        @if ($student->pivot->accepted)
                            <span class="glyphicon glyphicon-ok" title="Accepted">
                        @endif
                    </td>
                    <td>{{ $student->fullName() }}</td>
                    <td>
                    <div class="radio">
                      <label>
                        <input type="radio" name="accepted" id="optionsRadios1" value="{{ $student->id }}" @if ($student->pivot->accepted) checked disabled @endif>
                      </label>
                    </div>
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