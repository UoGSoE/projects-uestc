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
                    </td>
                    <td>{{ $location->courses->count() }}</td>
                    <td>0</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@stop