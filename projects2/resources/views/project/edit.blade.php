@extends('layout')

@section('content')

    <h2>
        Edit Project
        <a href="{!! action('ProjectController@duplicate', $project->id) !!}" class="btn btn-default">
            Copy This Project
        </a>
        @can('edit_projects')
            <form method="POST" action="{!! action('ProjectController@destroy', $project->id) !!}" class="pull-right">
                {{ csrf_field() }}
                <input type="hidden" name="_method" value="DELETE">
                <button type="submit" value="Delete" class="btn btn-danger" data-toggle="modal" data-target="#confirm-model">Delete</button>
            </form>
        @endcan

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