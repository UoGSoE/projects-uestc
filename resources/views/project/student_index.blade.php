@extends('layout')

@section('content')
<div class="page-header">
    <h1>
        <i>Hello</i>
        {{ Auth::user()->fullName() }}
    </h1>
    <a href="{!! route('student.profile_edit') !!}" class="btn btn-default">Edit my profile</a>
</div>
@if (Auth::user()->isAllocated())
    <p>
        You are allocated to the project "{{ Auth::user()->allocatedProject()->title }}".
        @include('project.partials.panel', ['project' => Auth::user()->allocatedProject()])
    </p>
@elseif (Auth::user()->projects()->count() > 0)
    <h2>Your choices</h2>
    @foreach (Auth::user()->projects as $project)
        @include('project.partials.panel', ['project' => $project])
    @endforeach
@else
    <h2>Available Projects</h2>
    <p>
        Please choose {{ $requiredUoGChoices }} UoG projects and {{ $requiredUESTCChoices }} UESTC projects.
    </p>
    <form method="POST" action="{!! route('choices.update') !!}" id="vueform">
        {{ csrf_field() }}
        <project-lista :projects="{{ Auth::user()->availableProjectsJson() }}" :allowselect="{{ $applicationsEnabled ? 1 : 0}}" :requireduestc="{{ $requiredUESTCChoices }}" :requireduog="{{ $requiredUoGChoices }}"></project-list>
    </form>
@endif
@endsection
@section('scripts')
<script>
    const app = new Vue({
        el: '#vueform',
    });
</script>
@endsection