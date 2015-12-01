@extends('layout')

@section('content')

    <h2>
        Edit Project
        <a href="{!! action('ProjectController@duplicate', $project->id) !!}" class="btn btn-default">
            Copy This Project
        </a>
        <a action-href="{!! action('ProjectController@destroy', $project->id) !!}" data-confirm="Really delete this project?" class="btn btn-danger pull-right data-confirm">
            Delete
        </a>
    </h2>
    <form method="POST" action="{!! action('ProjectController@update', $project->id) !!}">
        <input type="hidden" name="_method" value="PATCH">
        @include('project.partials.project_form')
        <p></p>
        <button type="submit" class="btn btn-primary">Update</button>
    </form>
        <script>
            $(document).ready(function() {
                $('.select2').select2();
            });
        </script>
@stop