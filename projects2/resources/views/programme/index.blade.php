@extends('layout')

@section('content')

    <h2>
        Programmes
        <a href="{!! action('ProgrammeController@create') !!}" class="btn btn-default">New Programme</a>
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
            @foreach ($programmes as $programme)
                <tr>
                    <td>{{ $programme->title }}</td>
                    <td>0</td>
                    <td>0</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@stop
