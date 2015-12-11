@extends('layout')

@section('content')
    <div class="container">
        <h2>
            Details for {{ $user->fullName() }}
            @can('edit_users')
                <a href="{!! action('UserController@edit', $user->id) !!}" class="btn btn-default">Edit</a>
            @endcan
            @can('login_as_user')
                <a href="{!! action('UserController@logInAs', $user->id) !!}" class="btn btn-warning pull-right">Log in as</a>
            @endcan
        </h2>
        <dl>
            <dt>Username</dt>
            <dd>{{ $user->username }}</dd>
            <dt>Email</dt>
            <dd><a href="mailto:{{ $user->email }}">{{ $user->email }}</a></dd>
            <dt>Type</dt>
            <dd>{{ $user->password ? 'External' : 'Internal' }} {{ $user->is_student ? 'Student' : 'Staff' }}</dd>
            <dt>Last Login</dt>
            <dd>{{ $user->last_login or 'Never'}}</dd>
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
            @if ($user->courses()->count() > 0)
                <dt>Enrolled On</dt>
                <dd>
                    <a href="{!! action('CourseController@show', $user->courses->first()->id) !!}">
                        {{ $user->courses->first()->title }}
                    </a>
                </dd>
            @endif
        </dl>
        <h2>Current Projects</h2>
        @if ($user->projects->count() == 0)
            None
        @else
            @foreach ($user->projects as $project)
                <li>
                    <a href="{!! action('ProjectController@show', $project->id) !!}">
                        {{ $project->title }}
                    </a> ({{ $project->students->count() }} Students)
            @endforeach
        @endif
    </div>
@stop
