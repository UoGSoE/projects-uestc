@extends('layout')

@section('content')
    <div class="container">
        <h2>
            Details for @if ($user->is_student) student @else staff @endif {{ $user->fullName() }}
            @can('edit_users')
                <a href="{!! action('UserController@edit', $user->id) !!}" class="btn btn-default">Edit</a>
            @endcan
            @can('edit_users')
                <div class="btn-group pull-right">

                <a href="{!! action('UserController@logInAs', $user->id) !!}" class="btn btn-warning">Log in as</a>
                @if(!$user->is_student and $user->usernameIsEmail())
                    @if($user->hasPasswordReset())
                        <a style="margin-right:10px" href="#" class="btn btn-outline btn-info" disabled>User has awaiting password reset email</a>
                    @else
                        <a style="margin-right:10px" href="{!! action('StaffController@sendPasswordEmail', $user->id) !!}" class="btn btn-info">Send Password Reset Email</a>
                    @endif
                @endif
                </div>
            @endcan
        </h2>
        <dl>
            <dt>Username</dt>
            <dd>{{ $user->username }}</dd>
            <dt>Email</dt>
            <dd><a href="mailto:{{ $user->email }}">{{ $user->email }}</a></dd>
            <dt>Institution</dt>
            <dd>{{ $user->institution }}</dd>
            <dt>Type</dt>
            <dd>{{ $user->password ? 'External' : 'Internal' }} {{ $user->is_student ? 'Student' : 'Staff' }}</dd>
            <dt>Last Login</dt>
            <dd>{{ $user->last_login ?? 'Never'}}</dd>
            <dt>Roles</dt>
            <dd>
                @if ($user->hasRoles())
                    @if ($user->isAdmin())
                        &middot; Site Admin
                    @endif
                    @if ($user->isConvenor())
                        &middot; Project Convenor
                    @endif
                @else
                    Regular User
                @endif
            </dd>
            @if ($user->is_student)
                <dt>Enrolled On</dt>
                @if ($user->courses()->count() > 0)
                    <dd>
                        <a href="{!! action('CourseController@show', $user->courses->first()->id) !!}">
                             {{ $user->courses->first()->code }} - {{ $user->courses->first()->title }}
                        </a>
                    </dd>
                @else
                    <dd>No course</dd>
                @endif
            @endif
        </dl>
        <h2>Current Projects
            @can('edit_users')
                @if ($user->is_student and $user->allocatedProject())
                    <form class="pull-right" method="POST" action="{{ route('student.unallocate', $user->id) }}">
                        {{ csrf_field() }}
                        <button class="btn btn-danger">Remove from allocated project</button>
                    </form>
                @endif
            @endcan
        </h2>
        @if ($user->projects->count() == 0)
            None
        @elseif ($user->is_student)
            @if ($user->projects()->UESTC()->count() > 0)
                <h5>UESTC Projects</h5>
                <ol>
                    @foreach ($user->projects()->UESTC()->orderBy('preference')->get() as $project)
                        <li>
                            <a href="{!! action('ProjectController@show', $project->id) !!}">
                                {{ $project->title }}
                            </a> ({{ $project->students->count() }} Students)
                            (Created {{ $project->created_at->format('d/m/Y') }} / Updated {{ $project->updated_at->format('d/m/Y' )}})
                        </li>
                    @endforeach
                </ol>
            @endif

            @if ($user->projects()->UoG()->count() > 0)
                <h5>UoG Projects</h5>
                <ol>
                    @foreach ($user->projects()->UoG()->orderBy('preference')->get() as $project)
                        <li>
                            <a href="{!! action('ProjectController@show', $project->id) !!}">
                                {{ $project->title }}
                            </a> ({{ $project->students->count() }} Students)
                            (Created {{ $project->created_at->format('d/m/Y') }} / Updated {{ $project->updated_at->format('d/m/Y' )}})
                        </li>
                    @endforeach
                </ol>
            @endif
        @else
            @foreach ($user->projects as $project)
                <li>
                    <a href="{!! action('ProjectController@show', $project->id) !!}">
                        {{ $project->title }}
                    </a> ({{ $project->students->count() }} Students)
                    (Created {{ $project->created_at->format('d/m/Y') }} / Updated {{ $project->updated_at->format('d/m/Y' )}})
                </li>
            @endforeach
        @endif

    </div>
@stop
