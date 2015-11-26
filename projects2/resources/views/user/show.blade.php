@extends('layout')

@section('content')
    <div class="container">
        <h2>
            Details for {{ $user->fullName() }}
            <a href="{!! action('UserController@edit', $user->id) !!}" class="btn btn-default">Edit</a>
        </h2>
        <dl>
            <dt>Username</dt>
            <dd>{{ $user->username }}</dd>
            <dt>Email</dt>
            <dd><a href="mailto:{{ $user->email }}">{{ $user->email }}</a></dd>
            <dt>Type</dt>
            <dd>{{ $user->password ? 'External' : 'Internal' }} {{ $user->is_student ? 'Student' : 'Staff' }}</dd>
            <dt>Roles</dt>
            @if ($user->roles()->count() > 0)
                <dd>
                    <ul class="list-inline">
                        @foreach ($user->roles as $role)
                            <li title="@foreach ($role->permissions as $permission) {{ $permission->label }}, @endforeach">{{ $role->label }}</li>
                        @endforeach
                    </ul>
                </dd>
            @else
                Regular User
            @endif
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
