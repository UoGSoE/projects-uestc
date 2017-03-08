@extends('layout')

@section('content')

    <h2>
        Projects
        <a href="{!! route('options.edit') !!}" class="btn btn-default pull-right">Options</a>
    </h2>
    <p>
        Filters :
        <label class="checkbox-inline">
            <input type="checkbox" id="only_active" value="1"> Active
        </label>
        <label class="checkbox-inline">
            <input type="checkbox" id="only_inactive" value="1"> Inactive
        </label>
    </p>
    <p>
        Type :
        <a href="{!! action('ReportController@allProjects') !!}">
            All
        </a>
        @foreach ($disciplines as $discipline)
            <a href="{!! action('ReportController@allProjectsOfDiscipline', $discipline->id) !!}">
                {{ $discipline->title }}
            </a>
        @endforeach
    </p>
    <table class="table table-striped table-hover datatable">
        <thead>
            <tr>
                <th>Title</th>
                <th>Owner</th>
                <th>Discipline</th>
                <th>1st round choices</th>
                <th>Rresult</th>
                <th>2nd round choices</th>
                <th>Result</th>
                <th>Student</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($projects as $project)
                <tr class="active_{{ $project->is_active }}">
                    <td>
                        @if (!$project->is_active) <del title="Not running"> @endif
                        <a href="{!! action('ProjectController@show', $project->id) !!}">
                            {{ $project->title }}
                        </a>
                        @if (!$project->is_active) </del> @endif
                    </td>
                    <td>
                        <a href="{!! action('UserController@show', $project->owner->id) !!}">
                            {{ $project->owner->fullName() }}
                        </a>
                    </td>
                    <td>
                        @if ($project->discipline)
                            <a href="{!! action('ReportController@allProjectsOfDiscipline', $project->discipline->id) !!}">
                                {{ $project->disciplineTitle() }}
                            </a>
                        @else
                            {{ $project->disciplineTitle() }}
                        @endif
                    </td>
                    <td applicants="round1-applicants-{{ $project->roundStudentCount(1) }}">
                        {{ $project->roundStudentCount(1) }}
                    </td>
                    <td applicants="round1-accepted-{{ $project->roundStudentAcceptedCount(1) }}">
                        @if ($project->roundStudentAcceptedCount(1) > 0)
                            Y
                        @else
                            N
                        @endif
                    </td>
                    <td applicants="round2-applicants-{{ $project->roundStudentCount(2) }}">
                        {{ $project->roundStudentCount(2) }}
                    </td>
                    <td applicants="round2-accepted-{{ $project->roundStudentAcceptedCount(2) }}">
                        @if ($project->roundStudentAcceptedCount(2) > 0)
                            Y
                        @else
                            N
                        @endif
                    </td>
                    <td>
                        @if ($project->numberAccepted() > 0)
                            @foreach ($project->acceptedStudents()->get() as $student)
                                {{ $student->fullname() }}
                            @endforeach
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@include('partials.datatables')
<script>
    $(document).ready(function() {
        $("#only_active").click(function() {
            if ($(this).prop('checked')) {
                $(".active_1").show();
                $(".active_0").hide();
            } else {
                $(".active_0").show();
            }
        });
        $("#only_inactive").click(function() {
            if ($(this).prop('checked')) {
                $(".active_0").show();
                $(".active_1").hide();
            } else {
                $(".active_1").show();
            }
        });
    });
</script>
@stop
