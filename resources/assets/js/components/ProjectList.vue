<template>
    <div>
        <div class="panel panel-default" v-for="project in projects" :key="project.id">
            <div class="panel-heading fake-link" :id="'title_' + project.id" @click="expandProject(project.id)">
                <h3 class="panel-title">
                    {{ project.title }} ({{ project.owner }})
                    <span v-if="project.discipline">
                        (field {{ project.discipline }})
                    </span>
                    <span style="float:right">
                        <img :src="'img/'+project.institution+'.png'" :alt="project.institution" height="20" width="30">
                    </span>
                </h3>
            </div>
            <div v-if="isExpanded(project.id)">
                <div class="panel-body" >
                    {{ project.description }}
                    <div v-if="project.prereq" class="help-block">
                        Prerequisites: {{ project.prereq }}
                    </div>
                </div>
                <ul class="list-group">
                    <li class="list-group-item" v-for="link in project.links" :key="link.id">
                        <a :href="link.url" target="_blank">
                            {{ link.url}}
                        </a>
                    </li>
                    <li class="list-group-item" v-for="file in project.files" :key="file.id">
                        <a :href="'/projectfile/' + file.id">
                            <span class="glyphicon glyphicon-download" aria-hidden="true"></span> {{ file.original_filename }}
                        </a>
                    </li>
                </ul>
                <div class="panel-footer" v-if="allowselect">
                    <button class="btn btn-sm" @click.prevent="choose(project)" :class="{'btn-success' : !project.chosen, 'btn-danger' : project.chosen}">
                        <span v-if="!project.chosen">Add</span>
                        <span v-else>Remove</span>
                    </button>
                    <div style="float:right; width:60%; margin-top:5px">
                        <div style="float:left; width:10%">
                            Popularity
                        </div>
                        <div class="progress" style="background-color:white; float:right; width:90%">
                            <div :class="'progress-bar '+ project.popularity.colour" role="progressbar" :aria-valuenow="project.popularity.percent"
                            aria-valuemin="0" aria-valuemax="100" :style="'min-width: 2em; max-width:100%; width:'+project.popularity.percent+'%'">
                                {{ project.popularity.caption }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <transition name="fade">
            <div v-if="anyProjectsChosen && expandedChoices">
                <div id="infobox" class="panel panel-success" :class="{'panel-danger': invalidChoices, 'panel-info': !allChosen}">
                    <div class="panel-heading">
                        {{ panelHeading }}
                        <span @click="expandChoices" style="float:right;" class="pointer glyphicon glyphicon-minus" aria-hidden="true"></span>
                    </div>
                    <div class="panel-body">
                        <span v-for="institution in ['uestc', 'uog']" :key="institution">
                            <h5>{{ institution.toUpperCase() }} Projects
                                <span
                                class="label label-default"
                                :class="{
                                    'label-success' : choices[institution].length == required[institution],
                                    'label-danger': choices[institution].length > required[institution] || invalidChoices
                                    }"
                                >
                                    {{ choices[institution].length }}/{{ required[institution] }}
                                </span>
                            </h5>
                            <draggable v-model="choices[institution]" @start="drag=true" @end="drag=false" :options="{disabled: !allChosen || !validChoices }">
                                <transition-group>
                                    <div v-for="project in choices[institution]" class="panel panel-default panel-choices" :class="{'move' : validChoices}" :key="project.id">
                                        <div class="panel-body container-fluid">
                                            <div class="row">
                                                <div class="col-md-1">
                                                    <img :src="'img/'+project.institution+'.png'" :alt="project.institution" height="20" width="30">
                                                </div>
                                                <div class="col-md-5">
                                                    {{ project.title }}
                                                </div>
                                                <div class="col-md-4">
                                                    {{ project.owner }}
                                                </div>
                                                <div class="col-md-1">
                                                    <button class="btn btn-xs btn-danger" @click.prevent="choose(project)">
                                                        Remove
                                                </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </transition-group>
                            </draggable>
                        </span>
                    </div>
                    <div v-if="validChoices" class="panel-footer" style="min-height:50px">
                        <div style="float:left; margin-top:5px;" class="checkbox">
                            <label>
                                <input v-model="confirmOrder" type="checkbox"> I confirm the order of my choices
                            </label>
                        </div>
                        <button
                            style="float:right"
                            class="btn btn-sm btn-success"
                            :class="{'btn-danger': submissionError}"
                            :disabled="!confirmOrder"
                            @click.prevent="submitChoices">
                            {{ submitButtonText }}
                        </button>
                    </div>
                </div>
            </div>
        </transition>

        <transition name="fade">
            <div id="infobox"
                v-if="!expandedChoices"
                class="panel panel-success"
                :class="{
                    'panel-danger': invalidChoices,
                    'panel-info': !allChosen
                    }"
                >
                <div class="panel-heading">
                    {{ panelHeading }}
                    <span @click="expandChoices" style="float:right;" class="pointer glyphicon glyphicon-plus" aria-hidden="true"></span>
                </div>
            </div>
        </transition>
    </div>
</template>

<script>
    export default {
        props: ['projects', 'allowselect', 'required'],

        data() {
            return {
                showConfirmation: false,
                openProjects: [],
                submitButtonText: 'Submit my choices',
                submissionError: false,
                choices: {
                    uestc: [],
                    uog: [],
                },
                choiceError: '',
                uniqueSupervisors: true,
                expandedChoices: true,
                confirmOrder: false,
                supervisors: []
            }
        },

        computed: {
            anyProjectsChosen() {
                return this.choices['uestc'].length > 0 || this.choices['uog'].length > 0;
            },

            numberOfUoG: function() {
                var total = 0;
                for (var key in this.choices['uog']) {
                    if (this.choices['uog'].hasOwnProperty(key)) {
                        if (this.choices['uog'][key] != null) {
                            total++;
                        }
                    }
                }
                return total;
            },

            numberOfUESTC: function() {
                var total = 0;
                for (var key in this.choices['uestc']) {
                    if (this.choices['uestc'].hasOwnProperty(key)) {
                        if (this.choices['uestc'][key] != null) {
                            total++;
                        }
                    }
                }
                return total;
            },

            allChosen: function() {
                if (this.numberOfUoG == this.required['uog'] && this.numberOfUESTC == this.required['uestc']) {
                    return true;
                }
                return false;
            },

            validChoices: function () {
                if (this.invalidChoices) {
                    return false;
                }
                if (!this.allChosen) {
                    return false;
                }
                return true;
            },

            invalidChoices: function() {
                if (this.uniqueSupervisors == false) {
                    this.choiceError = 'You cannot choose two projects with the same supervisor';
                    return true;
                }
                if (this.numberOfUoG > this.required['uog'] || this.numberOfUESTC > this.required['uestc']) {
                    this.choiceError = 'You must choose ' + this.required['uestc'] + ' UESTC projects and ' + this.required['uog'] + ' UOG projects.';
                    return true;
                }
                return false;
            },

            panelHeading: function() {
                if (this.invalidChoices) {
                    return this.choiceError;
                } else if (!this.allChosen) {
                    return 'Not chosen all.';
                }
                return 'You have chosen all projects - you can now submit your choices.';
            }
        },

        methods: {
            isExpanded: function (projectId) {
                if (this.openProjects.indexOf(projectId) != -1) {
                    return true;
                }
                return false;
            },

            expandProject: function (projectId) {
                if (this.isExpanded(projectId)) {
                    let index = this.openProjects.indexOf(projectId);
                    this.openProjects.splice(index, 1);
                    return;
                }
                this.openProjects.push(projectId);
            },

            expandChoices: function() {
                this.expandedChoices = ! this.expandedChoices;
            },

            choose: function (project) {
                project.chosen = ! project.chosen;
                if (project.chosen) {
                    this.supervisors.push(project.owner);
                    this.choices[project.institution.toLowerCase()].push(project);
                } else {
                    this.supervisors.splice(this.supervisors.indexOf(project.owner), 1);
                    this.choices[project.institution.toLowerCase()].splice(this.choices[project.institution.toLowerCase()].indexOf(project), 1);
                }
                if (this.supervisors.includes(project.owner)) {
                    this.uniqueSupervisors = this.supervisors.indexOf(project.owner) == this.supervisors.lastIndexOf(project.owner);
                }
            },

            submitChoices() {
                // var choices = {
                //     "1": this.choices.first,
                //     "2": this.choices.second,
                //     "3": this.choices.third,
                //     "4": this.choices.fourth,
                //     "5": this.choices.fifth,
                // };
                // console.log(choices);
                // axios.post(route('projects.choose'), {choices: choices})
                //      .then(response => {
                //         window.location = route('thank_you');
                //      })
                //      .catch(error => {
                //         this.submitButtonText = 'Error submitting choices - sorry';
                //         this.submissionError = true;
                //         console.log(error);
                //      });
            }
        }
    }
</script>
