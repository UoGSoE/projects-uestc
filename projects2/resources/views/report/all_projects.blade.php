@extends('layout')

@section('content')

    <h2>
        Projects
        <a href="{!! route('export.allocations') !!}" class="btn btn-default" title="Export as Excel">
            <span class="glyphicon glyphicon-download-alt" aria-hidden="true"></span>
        </a>
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
    @include('report.partials.project_list')
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
