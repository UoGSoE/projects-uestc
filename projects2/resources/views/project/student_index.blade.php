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
    </p>
@elseif (Auth::user()->projects()->count() > 0)
    <h2>Your choices</h2>
    @foreach (Auth::user()->projects as $project)
        @include('project.partials.panel', ['project' => $project])
    @endforeach
@else
    <h2>Available Projects</h2>
    <p>
        Please choose {{ $requiredProjectChoices }} projects.
    </p>
    <form method="POST" action="{!! route('choices.update') !!}" id="vueform">
        {{ csrf_field() }}
        <project-list :projects="projects" :allowselect="allowSelect"></project-list>
        <button :disabled="invalidChoices">
            @{{ buttonText }}
        </button>

    </form>

<script src="/vendor/vuejs_2.1.10.js"></script>
<script>

window.Event = new Vue();

Vue.component('project-detail', {
    props: ['project', 'allowselect'],
    data() {
        return {
            showDetails: false,
        }
    },
    template: `
    <div>
    <div class="panel panel-default">
        <div class="panel-heading fake-link" :id="'title_' + project.id" @click="toggleDetails">
            <h3 class="panel-title">
                @{{ project.title }} (@{{ project.owner }})
                <span v-if="project.discipline">
                    (field @{{ project.discipline }})
                </span>
            </h3>
        </div>
        <transition name="fade">
        <div v-if="showDetails">
            <div class="panel-body" >
                @{{ project.description }}
                <div class="help-block">
                    Prerequisites: @{{ project.prereq }}
                </div>
            </div>
            <ul class="list-group">
                <li class="list-group-item" v-for="link in project.links">
                    <a :href="link.url" target="_blank">
                        @{{ link.url}}
                    </a>
                </li>
                <li class="list-group-item" v-for="file in project.files">
                    <a :href="'/projectfile/' + file.id">
                        <span class="glyphicon glyphicon-download" aria-hidden="true"></span> @{{ file.original_filename }}
                    </a>
                </li>
            </ul>
            <div class="panel-footer" v-if="allowselect">
                <div class="checkbox">
                    <label>
                      <input type="checkbox" :id="'choose_' + project.id" name="choices[]" :value="project.id" @click="updateChoice"> Apply
                    </label>
                </div>
            </div>
        </div>
        </transition>
    </div>
    </div>
    `,
    methods: {
        updateChoice: function() {
            Event.$emit('chosen', this.project.id)
        },
        toggleDetails: function() {
            this.showDetails = !this.showDetails;
        }
    }
});

Vue.component('project-list', {
    props: ['projects', 'allowselect'],
    template: `
        <div>
            <project-detail v-for="project in projects" :project="project" :key="project.id" :allowselect="allowselect" :discipline="project.discipline_css">
            </project-detail>
        </div>
    `,
});

var app = new Vue({
    el: '#vueform',
    data: {
        projects: {!! Auth::user()->availableProjectsJson() !!},
        allowSelect: {{ $applicationsEnabled }},
        requiredChoices: {{ $requiredProjectChoices }},
    },
    methods: {
        toggleChoice(projectId) {
            let project = this.projects.find((project) => {
                return project.id == projectId;
            });
            if (project) {
                project.chosen = ! project.chosen;
            }
        }
    },
    computed: {
        chosenCount: function() {
            return this.projects.reduce(function(prevVal, project) {
                return prevVal + project.chosen;
            }, 0);
        },
        invalidChoices: function() {
            return this.chosenCount != this.requiredChoices;
        },
        buttonText: function() {
            if (this.invalidChoices) {
                return 'You must choose ' + this.requiredChoices + ' projects';
            }
            return 'Submit your choices';
        }
    },
    created() {
        Event.$on('chosen', this.toggleChoice);
    }
});
</script>

@endif

@endsection
