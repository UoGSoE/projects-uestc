@extends('layout')

@section('content')
    <h2>
        Your Projects
        <a href="{!! route('project.create') !!}" class="btn btn-default">New Project</a>
    </h2>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Title</th>
                <th>Students</th>
                <th>Disciplines</th>
            </tr>
        </thead>
        <tbody>
            @foreach (Auth::user()->projects as $project)
                <tr>
                    <td>
                        <a href="{!! route('project.show', $project->id) !!}">
                            @if ($project->is_active)
                                {{ $project->title }}
                            @else
                                <del title="Not Active">{{ $project->title }}</del>
                            @endif
                        </a>
                    </td>
                    <td>{{ $project->students->count() }} applied, {{ $project->numberAccepted() }} accepted</td>
                    <td>
                        @if ($project->discipline_id)
                            {{ $project->disciplineTitle() }}
                        @elseif ($project->disciplines->count())
                            <ul class="list-inline">
                                @foreach ($project->disciplines as $discipline)
                                    <li>
                                        {{ $discipline->title }}
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            N/A
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
