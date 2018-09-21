<template>
    <div>
        <input type="hidden" name="uestcChoices" v-model="uestcChoices">
        <input type="hidden" name="uogChoices" v-model="uogChoices">
        <div v-for="(discipline, index) in projects">
            <h3>{{ index }}</h3>

            <div class="panel panel-default" v-for="project in discipline" :key="project.id">
                <div class="panel-heading fake-link" :id="'title_' + project.id" @click="expandProject(project.id, index)">
                    <h3 class="panel-title">
                        {{ project.title }} ({{ project.owner }})
                        <span v-if="project.discipline">
                            ({{ project.discipline.trim() }})
                        </span>
                        <span style="float:right">
                            <img :src="'img/'+project.institution+'.png'" :alt="project.institution" height="20" width="30">
                        </span>
                    </h3>
                </div>
                <div v-if="isExpanded(project.id, index)">
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
            <hr>

            <transition name="fade">
                <div v-if="anyProjectsChosen && expandedChoices">
                    <div id="infobox" class="panel panel-success" :class="{'panel-danger': invalidChoices, 'panel-info': !allChosen}">
                        <div @click="expandChoices" class="pointer panel-heading">
                            {{ panelHeading }}
                            <span style="float:right;" class="glyphicon glyphicon-minus" aria-hidden="true"></span>
                        </div>
                        <div class="panel-body">
                            {{ instructions }}
                            <span v-for="institution in ['uestc', 'uog']" :key="institution">
                                <h5 v-if="required[institution] > 0">{{ institution.toUpperCase() }} Projects
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
                                        <div v-for="(project, index) in choices[institution]" class="panel panel-default panel-choices" :class="{'move' : validChoices }" :key="project.id">
                                            <div class="panel-body container-fluid">
                                                <div class="row">
                                                    <div class="col-md-1">
                                                        <img :src="'img/'+project.institution+'.png'" :alt="project.institution" height="20" width="30">
                                                    </div>
                                                    <div class="col-md-5">
                                                        {{ index + 1 }}. <abbr style="border-bottom:0px" :title="project.title">{{ shortTitle(project.title) }}</abbr>
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
                                    <input v-model="confirmOrder" type="checkbox"> {{ checkboxLabel }}
                                </label>
                            </div>
                            <button
                                style="float:right"
                                class="btn btn-sm btn-success"
                                :class="{'btn-danger': submissionError}"
                                :disabled="!confirmOrder">
                                {{ submitButtonText }}
                            </button>
                        </div>
                    </div>
                </div>
            </transition>
        </div>

        <transition name="fade">
            <div id="infobox"
                v-if="!expandedChoices"
                class="panel panel-success"
                :class="{
                    'panel-danger': invalidChoices,
                    'panel-info': !allChosen
                    }"
                >
                <div @click="expandChoices" class="pointer panel-heading">
                    {{ panelHeading }}
                    <span style="float:right;" class="glyphicon glyphicon-plus" aria-hidden="true"></span>
                </div>
            </div>
        </transition>
    </div>
</template>

<script>
    export default {
        props: ['projects', 'allowselect', 'required', 'uniquesupervisorsrules'],

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
                uniqueSupervisors: {
                    UESTC: true,
                    UoG: true
                },
                expandedChoices: true,
                confirmOrder: false,
                supervisors: {
                    UESTC: [],
                    UoG: [],
                }
            }
        },

        computed: {
            uestcChoices() {
                if (this.required['uestc'] > 0) {
                    return this.choices['uestc'].map( function(element) {
                        return element['id'];
                    });
                }
                return null;
            },
            uogChoices() {
                if (this.required['uog'] > 0) {
                    return this.choices['uog'].map( function(element) {
                        return element['id'];
                    });
                }
                return null;
            },
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
                if (this.uniqueSupervisors['UESTC'] == false) {
                    this.choiceError = 'You cannot choose two UESTC projects with the same supervisor';
                    return true;
                }
                if (this.uniqueSupervisors['UoG'] == false) {
                    this.choiceError = 'You cannot choose two UoG projects with the same supervisor';
                    return true;
                }
                if (this.numberOfUoG > this.required['uog'] || this.numberOfUESTC > this.required['uestc']) {
                    if (this.required['uestc'] > 0 && this.required['uog'] > 0) {
                        this.choiceError = 'You must choose ' + this.required['uestc'] + ' UESTC projects and ' + this.required['uog'] + ' UOG projects.';
                    } else if (this.required['uestc'] > 0 && this.required['uog'] > 0) {
                        this.choiceError = 'You must choose ' + this.required['uestc'] + ' UESTC projects.';
                    } else if (this.required['uestc'] <= 0 && this.required['uog'] > 0) {
                        this.choiceError = 'You must choose ' + this.required['uog'] + ' UOG projects.';
                    } else {
                        this.choiceError = 'Error';
                    }
                    return true;
                }
                return false;
            },

            panelHeading: function() {
                if (this.invalidChoices) {
                    return this.choiceError;
                } else if (!this.allChosen) {
                    return 'Project choices';
                }
                return 'Please arrange your choices into the order of your most preferred.';
            },

            instructions: function() {
                if (!this.invalidChoices && this.allChosen) {
                    return 'Click and drag to sort your choices in order of preferences (top choice being most preferred).'
                }
                return ''
            },

            checkboxLabel: function() {
                return 'I confirm the preference order of my choices';
            }

        },

        methods: {
            shortTitle: function (title) {
                var maxLength = 30;
                var ending = '...'
                if (title.length > maxLength) {
                    return title.substring(0, maxLength - ending.length) + ending;
                } else {
                    return title;
                }
            },
            isExpanded: function (projectId, discipline) {
                if (this.openProjects.indexOf(projectId + discipline) != -1) {
                    return true;
                }
                return false;
            },

            expandProject: function (projectId, discipline) {
                if (this.isExpanded(projectId, discipline)) {
                    let index = this.openProjects.indexOf(projectId + discipline);
                    this.openProjects.splice(index, 1);
                    return;
                }
                this.openProjects.push(projectId + discipline);
            },

            expandChoices: function() {
                this.expandedChoices = ! this.expandedChoices;
            },

            choose: function (project) {
                for (var disciplineKey in this.projects) {
                    var discipline = this.projects[disciplineKey]
                    for (var projectKey in discipline) {
                        var projectOb = this.projects[disciplineKey][projectKey];
                        if (projectOb.id == project.id) {
                            projectOb.chosen = ! projectOb.chosen;
                        }
                    }
                }

                if (project.chosen) {
                    if (this.supervisors[project.institution].includes(project.owner)) {
                        if (this.uniquesupervisorsrules[project.institution]) {
                            this.uniqueSupervisors[project.institution] = false;
                        }
                    }
                    this.supervisors[project.institution].push(project.owner);
                    this.choices[project.institution.toLowerCase()].push(project);
                } else {
                    this.supervisors[project.institution].splice(this.supervisors[project.institution].indexOf(project.owner), 1);
                    this.choices[project.institution.toLowerCase()].splice(this.choices[project.institution.toLowerCase()].indexOf(project), 1);
                    if (this.supervisors[project.institution].includes(project.owner)) {
                        if (this.uniquesupervisorsrules[project.institution]) {
                            this.uniqueSupervisors[project.institution] = this.supervisors[project.institution].indexOf(project.owner) == this.supervisors[project.institution].lastIndexOf(project.owner);
                        }
                    }
                }
            },
        }
    }
</script>
