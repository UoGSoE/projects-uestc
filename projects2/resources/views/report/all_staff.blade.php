@extends('layout')

@section('content')

<h2>Staff</h2>
<table class="table table-striped table-hover">
    <thead>
        <tr>
            <th>Name</th>
            <th>Projects</th>
            <th>Applied</th>
            <th>Accepted</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($users as $user)
            <tr>
                <td>
                    @can('view_users')
                        <a href="{!! action('UserController@show', $user->id) !!}">
                            {{ $user->fullName() }}
                        </a>
                    @else
                        {{ $user->fullName() }}
                    @endif
                </td>
                <td>{{ $user->projects->count() }}</td>
                <td>{{ $user->totalStudents() }}</td>
                <td>{{ $user->totalAcceptedStudents() }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
@stop