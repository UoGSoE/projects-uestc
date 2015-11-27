@extends('layout')

@section('content')

    <h2>Edit Project</h2>
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