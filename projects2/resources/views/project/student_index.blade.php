@extends('layout')

@section('content')
<div class="page-header">
    <h1>
        <i>Hello</i>
        {{ Auth::user()->fullName() }}
    </h1>
    <a href="{!! route('student.profile_edit') !!}" class="btn btn-default">Edit my profile</a>
</div>
<h2>Available Projects</h2>
<p>
    Please choose {{ config('projects.requiredProjectChoices') }} projects.
</p>
<form method="POST" action="{!! route('choices.update') !!}" id="vueform">
    {{ csrf_field() }}
    @foreach (Auth::user()->availableProjects() as $project)
        @if ($project->isAvailable())
            <div class="panel panel-default @if ($project->discipline) {{ $project->discipline->cssTitle() }} @endif">
                <div class="panel-heading fake-link" @click="showDetails({{ $project->id }})">
                    <h3 class="panel-title titlebox-{{ $project->id }}">
                        {{ $project->title }} ({{ $project->owner->fullName() }})
                        @if ($project->discipline) 
                            (field {{ $project->discipline->title }})
                        @endif
                    </h3>
                </div>
                <div class="panel-body" v-if="projectVisible({{ $project->id}})">
                    {{ $project->description }}
                    @if ($project->links()->count() > 0)
                        Links:
                        <ul>
                            @foreach ($project->links as $link)
                                <li><a href="{{ $link->url }}">{{ $link->url }}</a></li>
                            @endforeach
                        </ul>
                    @endif
                    <div class="help-block" v-if="projectVisible({{ $project->id}})">
                        Prerequisites: {{ $project->prereq or 'None' }}
                    </div>
                </div>
                @if ($applicationsEnabled)
                    <div class="panel-footer" v-if="projectVisible({{ $project->id}})">
                        <label class="radio-inline">
                            <input type="radio" id="choose_{{ $project->id }}" name="choices[]" value="{{ $project->id }}" @click="toggleChoice({{$project->id}})"> Apply
                        </label>
                    </div>
                @endif
            </div>
        @endif
    @endforeach
    <button type="submit" id="submit" class="btn btn-primary" v-if="validChoicesMade">
        Submit Choices
    </button>
</form>
<script src="/vendor/vuejs_2.1.10.js"></script>
<script>
    var app = new Vue({
      el: '#vueform',
      data: {
        choices: [],
        requiredChoices: {{ config('projects.requiredProjectChoices') }},
        visibleProjects: [],
      },
      methods: {
        toggleChoice: function(choice) {
            if (this.choices.indexOf(choice) >= 0) {
                index = this.choices.indexOf(choice);
                this.choices.splice(index, 1);
                return;
            }
            this.choices.push(choice);
        },
        showDetails: function(projectId) {
            this.visibleProjects.push(projectId);
        },
        projectVisible: function(projectId) {
            return this.choices.indexOf(projectId) >= 0;
        }
      },
      computed: {
        validChoicesMade: function() {
            return this.choices.length == this.requiredChoices;
        }
      }
    });
</script>
@endsection
