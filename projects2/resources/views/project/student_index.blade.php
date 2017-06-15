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
                <span style="float:right">
                    <img :src="'img/'+project.institution+'.png'" :alt="project.institution" height="20" width="30">
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
                <div style="height:20px;">
                    <div class="progress" style="float:left; width:50%; background-color:white">
                        <div :class="'progress-bar '+ project.popularity.colour" role="progressbar" :aria-valuenow="project.popularity.percent"
                          aria-valuemin="0" aria-valuemax="100" :style="'min-width: 2em; max-width:100%; width:'+project.popularity.percent+'%'">
                            @{{ project.popularity.caption }}
                        </div>
                    </div>
                    <div class="checkbox" style="float:right; margin-top:0px;">
                        <label>
                          <input type="checkbox" :id="'choose_' + project.id" name="choices[]" :value="project.id" @click="updateChoice"> Apply
                        </label>
                    </div>
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
        requiredUoGChoices: {{ $requiredUoGChoices }},
        requiredUESTCChoices: {{ $requiredUESTCChoices }},
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
            return this.numberOfUoG != this.requiredUoGChoices || this.numberOfUESTC != this.requiredUESTCChoices;
        },
        buttonText: function() {
            if (this.invalidChoices) {
                return 'You must choose ' + this.requiredUoGChoices + ' UoG projects and ' + this.requiredUESTCChoices + ' UESTC projects.';
            }
            return 'Submit your choices';
        },
        numberOfUoG: function() {
            return this.projects.reduce(function(prevVal, project) {
                if (!project.chosen) {
                    return prevVal;
                }
                return prevVal + (project.institution === 'UoG' ? 1 : 0);
            }, 0);
        },
        numberOfUESTC: function() {
            return this.projects.reduce(function(prevVal, project) {
                if (!project.chosen) {
                    return prevVal;
                }
                return prevVal + (project.institution === 'UESTC' ? 1 : 0);
            }, 0);
        }
    },
    created() {
        Event.$on('chosen', this.toggleChoice);
    }
});
</script>

@endif

@endsection
