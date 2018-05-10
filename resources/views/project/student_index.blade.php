@extends('layout')

@section('content')
    <div class="page-header">
        <h1>
            <i>Hello</i>
            {{ auth()->user()->fullName() }}
        </h1>
        <a href="{!! route('student.profile_edit') !!}" class="btn btn-default">Edit my profile</a>
    </div>
    @if (auth()->user()->isAllocated())
        <p>
            You are allocated to the project "{{ auth()->user()->allocatedProject()->title }}".
            @include('project.partials.panel', ['project' => auth()->user()->allocatedProject()])
        </p>
    @elseif (auth()->user()->projects()->count() > 0)
        <h2>Your choices</h2>
        @foreach (auth()->user()->projects as $project)
            @include('project.partials.panel', ['project' => $project])
        @endforeach
    @else
        <h2>Available Projects</h2>
        <p>
            Please choose {{ $required['uog'] }} UoG projects and {{ $required['uestc'] }} UESTC projects.
        </p>
        <form method="POST" action="{!! route('choices.update') !!}" id="vueform">
            {{ csrf_field() }}
            <project-list
                :projects="{{ auth()->user()->availableProjectsJson() }}"
                :allowselect="{{ $applicationsEnabled ? 1 : 0}}"
                :required="{{ json_encode($required) }}"
                :uniquesupervisorsrules="{{ json_encode($unique_supervisors) }}"
                >
            </project-list>
        </form>
    @endif
@endsection
@section('scripts')
    @if (auth()->user()->projects()->count() == 0)
        <script>
            const app = new Vue({
                el: '#vueform',
            });
        </script>
    @endif
@endsection