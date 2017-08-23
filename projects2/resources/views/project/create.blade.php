@extends('layout')

@section('content')

    <h2>Create A New Project</h2>
    <form method="POST" action="{!! action('ProjectController@store') !!}" enctype="multipart/form-data">
        @include('project.partials.project_form')
        <p></p>
        <button type="submit" class="btn btn-primary">Create</button>
    </form>
        <script>
            $(document).ready(function() {
                $('.select2').select2();
            });
        </script>
@stop