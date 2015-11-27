@extends('layout')

@section('content')

    <h2>Projects</h2>
    <p>
        Filters :
        <label class="checkbox-inline">
            <input type="checkbox" id="only_active" value="1"> Active
        </label>
        <label class="checkbox-inline">
            <input type="checkbox" id="only_inactive" value="1"> Inactive
        </label>
    </p>
    <table class="table table-striped table-hover datatable">
        <thead>
            <tr>
                <th>Title</th>
                <th>Owner</th>
                <th>Type</th>
                <th>Location</th>
                <th title="Max, Applied, Accepted">Students</th>
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
                    <td>{{ $project->type->title }}</td>
                    <td>{{ $project->location->title }}</td>
                    <td>
                        {{ $project->maximum_students }},
                        {{ $project->students->count() }},
                        {{ $project->acceptedStudents->count() }}
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
