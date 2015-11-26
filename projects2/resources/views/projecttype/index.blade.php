@extends('layout')

@section('content')

    <h2>
        Project Types
        <a href="{!! action('ProjectTypeController@create') !!}" class="btn btn-default">New Type</a>
    </h2>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Title</th>
                <th>Projects</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($types as $type)
                <tr>
                    <td>
                        <a href="{!! action('ProjectTypeController@edit', $type->id) !!}">
                            {{ $type->title }}
                        </a>
                    </td>
                    <td>{{ $type->projects->count() }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@stop