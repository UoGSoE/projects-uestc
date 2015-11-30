@extends('layout')

@section('content')

    <h2>
        Locations
        <a href="{!! action('LocationController@create') !!}" class="btn btn-default">New Location</a>
    </h2>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Title</th>
                <th>Courses</th>
                <th>Projects</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($locations as $location)
                <tr>
                    <td>
                        <a href="{!! action('LocationController@edit', $location->id) !!}">
                            {{ $location->title }}
                        </a>
                        @if ($location->is_default)
                            <span class="glyphicon glyphicon-ok" title="Default">
                        @endif
                    </td>
                    <td>{{ $location->courses->count() }}</td>
                    <td>{{ $location->activeProjects->count() }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@stop