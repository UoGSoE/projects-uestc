@extends('layout')

@section('content')
    <h2>
        Your Projects
        <a href="{!! action('ProjectController@create') !!}" class="btn btn-default">New Project</a>
    </h2>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Title</th>
                <th>Students</th>
                <th>Type</th>
            </tr>
        </thead>
        <tbody>
            @foreach (Auth::user()->projects as $project)
                <tr>
                    <td>
                        <a href="{!! action('ProjectController@show', $project->id) !!}">
                            @if ($project->is_active)
                                {{ $project->title }}
                            @else
                                <del title="Not Active">{{ $project->title }}</del>
                            @endif
                        </a>
                    </td>
                    <td>{{ $project->students->count() }} ({{ $project->maximum_students }} max)</td>
                    <td>{{ $project->disciplineTitle() }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection