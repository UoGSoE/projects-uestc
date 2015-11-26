@extends('layout')

@section('content')

    <h2>
        Your Projects
        <a href="{!! action('ProjectController@create') !!}" class="btn btn-default">New Project</a>
    </h2>
    @foreach (Auth::user()->projects as $project)
        <li>{{ $project->title }}</li>
    @endforeach
@stop