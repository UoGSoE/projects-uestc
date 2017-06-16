
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
