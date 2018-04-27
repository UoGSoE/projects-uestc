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
                    <li class="list-group-item" v-for="link in project.links" :key="link">
                        <a :href="link.url" target="_blank">
                            {{ link.url}}
                        </a>
                    </li>
                    <li class="list-group-item" v-for="file in project.files" :key="link">
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
            <div v-if="anyProjectsChosen">
                <div id="infobox" class="panel panel-success" :class="{'panel-danger': invalidChoices, 'panel-info': !allChosen}">
                    <div class="panel-heading">{{ panelHeading }}</div>
                    <div class="panel-body">
                        <h5>UESTC Projects <span class="label label-default" :class="{'label-success' : uestcChoices.length == requireduestc, 'label-danger': uestcChoices.length > requireduestc }">{{ uestcChoices.length }}/{{ requireduestc }}</span></h5>
                        <div v-for="project in uestcChoices" :key="project.id" class="panel panel-default panel-choices" :class="{'movable' : validChoices}">
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

                        <h5>UoG Projects <span class="label label-default" :class="{'label-success' : uogChoices.length == requireduog, 'label-danger': uogChoices.length > requireduog }">{{ uogChoices.length }}/{{ requireduog }}</span></h5>
                        <div v-for="project in uogChoices" :key="project.id" class="panel panel-default panel-choices" :class="{'movable' : validChoices}">
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


                    </div>
                    <div v-if="validChoices" class="panel-footer">
                        <button class="button is-info" :class="{'is-danger': submissionError}" :disabled="submissionError" @click.prevent="submitChoices">
                            {{ submitButtonText }}
                        </button>
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>

<script>
    export default {
        props: ['projects', 'allowselect', 'requireduestc', 'requireduog'],

        data() {
            return {
                showConfirmation: false,
                openProjects: [],
                submitButtonText: 'Submit my choices',
                submissionError: false,
                uestcChoices: [],
                uogChoices: [],
                supervisors: [],
                choiceError: '',
                uniqueSupervisors: true,
            }
        },

        computed: {
            anyProjectsChosen() {
                return this.uestcChoices.length > 0 || this.uogChoices.length > 0;
            },
            numberOfUoG: function() {
                var total = 0;
                for (var key in this.uogChoices) {
                    if (this.uogChoices.hasOwnProperty(key)) {
                        if (this.uogChoices[key] != null) {
                            total++;
                        }
                    }
                }
                return total;
            },
            numberOfUESTC: function() {
                var total = 0;
                for (var key in this.uestcChoices) {
                    if (this.uestcChoices.hasOwnProperty(key)) {
                        if (this.uestcChoices[key] != null) {
                            total++;
                        }
                    }
                }
                return total;
            },
            allChosen: function() {
                if (this.numberOfUoG == this.requireduog && this.numberOfUESTC == this.requireduestc) {
                    return true;
                }
                return false;
            },
            invalidChoices: function() {
                if (this.uniqueSupervisors == false) {
                    this.choiceError = 'You cannot choose two projects with the same supervisor';
                    return true;
                }
                if (this.numberOfUoG > this.requireduog || this.numberOfUESTC > this.requireduestc) {
                    this.choiceError = 'You must choose ' + this.requireduestc + ' UESTC projects and ' + this.requireduog + ' UOG projects.';
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

            choose: function (project) {
                project.chosen = ! project.chosen;
                if (project.chosen) {
                    this.supervisors.push(project.owner);
                    if (project.institution == 'UESTC') {
                        this.uestcChoices.push(project);
                    }
                    else {
                        this.uogChoices.push(project);
                    }
                } else {
                    this.supervisors.splice(this.supervisors.indexOf(project.owner), 1);
                    if (project.institution == 'UESTC') {
                        this.uestcChoices.splice(this.uestcChoices.indexOf(project), 1);
                    } else {
                        this.uogChoices.splice(this.uogChoices.indexOf(project), 1);
                    }
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
