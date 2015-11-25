@extends('layout')

@section('content')
    <div class="container">
        <h2>
            Details for {{ $user->fullName() }}
            [<a href="{!! action('UserController@edit', $user->id) !!}">Edit</a>]
        </h2>
        <dl>
            <dt>Username</dt>
            <dd>{{ $user->username }}</dd>
            <dt>Email</dt>
            <dd><a href="{{ $user->email }}">{{ $user->email }}</a></dd>
            <dt>Type</dt>
            <dd>{{ $user->password ? 'External' : 'Internal' }}</dd>
            <dt>Roles</dt>
            <dd>
                <ul class="list-inline">
                    @foreach ($user->roles as $role)
                        <li>{{ $role->label }}</li>
                    @endforeach
                </ul>
            </dd>
        </dl>
        <h2>Current Projects</h2>
        @if ($user->projects->count() == 0)
            None
        @else
            @foreach ($user->projects as $project)
                <li>{{ $project->title }} ({{ $project->students->count() }} Students)
            @endforeach
        @endif
    </div>
@stop
