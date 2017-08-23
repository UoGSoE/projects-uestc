@extends('layout')

@section('content')

    <h2>
        Edit Project
        <a href="{!! route('project.copy', $project->id) !!}" class="btn btn-default">
            Copy This Project
        </a>
        <a action-href="{!! action('ProjectController@destroy', $project->id) !!}" data-confirm="Really delete this project?" class="btn btn-danger pull-right data-confirm">
            Delete
        </a>
    </h2>
    <form method="POST" action="{!! route('project.update', $project->id) !!}" enctype="multipart/form-data">
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