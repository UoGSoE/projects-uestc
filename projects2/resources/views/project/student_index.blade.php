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
        <div style="margin-top:50px" class="navbar navbar-default navbar-fixed-top courses-bar" hidden>
            <div class="container">
                <div class="navbar-header" style="font-size:18px">
                    <div style="float:left; margin-right: 40px">
                        <img :src="'img/UoG.png'" alt="UoG" height="30" width="55"> @{{ numberOfUoG }}/@{{ requiredUoGChoices }}
                    </div>
                    <div style="display:inline-block;">
                        <img :src="'img/UESTC.png'" alt="UESTC" height="30" width="55"> @{{ numberOfUESTC }}/@{{ requiredUESTCChoices }}
                    </div>
                </div>
                <div class="navbar-collapse collapse">
                    <ul class="nav navbar-nav navbar-right">
                        <button :disabled="invalidChoices" class="btn">@{{ buttonText }}</button>
                    </ul>
                </div>
            </div>
        </div>
        <project-list :projects="projects" :allowselect="allowSelect"></project-list>
        <button :disabled="invalidChoices">
            @{{ buttonText }}
        </button>
    </form>

<script>
$( document ).ready(function() {
    $(".courses-bar").show();
});
</script>
<script src="/vendor/vuejs_2.1.10.js"></script>
<script>
    global = {
        projects: {!! Auth::user()->availableProjectsJson() !!},
        allowSelect: {{ $applicationsEnabled }},
        requiredUoGChoices: {{ $requiredUoGChoices }},
        requiredUESTCChoices: {{ $requiredUESTCChoices }},
    };
</script>
<script src="/js/student_project_chooser.js"></script>

@endif

@endsection
