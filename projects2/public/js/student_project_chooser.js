/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId])
/******/ 			return installedModules[moduleId].exports;
/******/
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// identity function for calling harmony imports with the correct context
/******/ 	__webpack_require__.i = function(value) { return value; };
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 2);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

/* WEBPACK VAR INJECTION */(function(global) {window.Event = new Vue();

Vue.component('project-detail', {
    props: ['project', 'allowselect'],
    data: function data() {
        return {
            showDetails: false
        };
    },

    template: '\n    <div>\n    <div class="panel panel-default">\n        <div class="panel-heading fake-link" :id="\'title_\' + project.id" @click="toggleDetails">\n            <h3 class="panel-title">\n                @{{ project.title }} (@{{ project.owner }})\n                <span v-if="project.discipline">\n                    (field @{{ project.discipline }})\n                </span>\n                <span style="float:right">\n                    <img :src="\'img/\'+project.institution+\'.png\'" :alt="project.institution" height="20" width="30">\n                </span>\n            </h3>\n        </div>\n        <transition name="fade">\n        <div v-if="showDetails">\n            <div class="panel-body" >\n                @{{ project.description }}\n                <div class="help-block">\n                    Prerequisites: @{{ project.prereq }}\n                </div>\n            </div>\n            <ul class="list-group">\n                <li class="list-group-item" v-for="link in project.links">\n                    <a :href="link.url" target="_blank">\n                        @{{ link.url}}\n                    </a>\n                </li>\n                <li class="list-group-item" v-for="file in project.files">\n                    <a :href="\'/projectfile/\' + file.id">\n                        <span class="glyphicon glyphicon-download" aria-hidden="true"></span> @{{ file.original_filename }}\n                    </a>\n                </li>\n            </ul>\n            <div class="panel-footer" v-if="allowselect">\n                <div style="height:20px;">\n                    <div class="progress" style="float:left; width:50%; background-color:white">\n                        <div :class="\'progress-bar \'+ project.popularity.colour" role="progressbar" :aria-valuenow="project.popularity.percent"\n                          aria-valuemin="0" aria-valuemax="100" :style="\'min-width: 2em; max-width:100%; width:\'+project.popularity.percent+\'%\'">\n                            @{{ project.popularity.caption }}\n                        </div>\n                    </div>\n                    <div class="checkbox" style="float:right; margin-top:0px;">\n                        <label>\n                          <input type="checkbox" :id="\'choose_\' + project.id" name="choices[]" :value="project.id" @click="updateChoice"> Apply\n                        </label>\n                    </div>\n                </div>\n            </div>\n        </div>\n        </transition>\n    </div>\n    </div>\n    ',
    methods: {
        updateChoice: function updateChoice() {
            Event.$emit('chosen', this.project.id);
        },
        toggleDetails: function toggleDetails() {
            this.showDetails = !this.showDetails;
        }
    }
});

Vue.component('project-list', {
    props: ['projects', 'allowselect'],
    template: '\n        <div>\n            <project-detail v-for="project in projects" :project="project" :key="project.id" :allowselect="allowselect" :discipline="project.discipline_css">\n            </project-detail>\n        </div>\n    '
});

var app = new Vue({
    el: '#vueform',
    data: {
        projects: global.projects,
        allowSelect: global.applicationsEnabled,
        requiredUoGChoices: global.requiredUoGChoices,
        requiredUESTCChoices: global.requiredUESTCChoices
    },
    methods: {
        toggleChoice: function toggleChoice(projectId) {
            var project = this.projects.find(function (project) {
                return project.id == projectId;
            });
            if (project) {
                project.chosen = !project.chosen;
            }
        }
    },
    computed: {
        chosenCount: function chosenCount() {
            return this.projects.reduce(function (prevVal, project) {
                return prevVal + project.chosen;
            }, 0);
        },
        invalidChoices: function invalidChoices() {
            return this.numberOfUoG != this.requiredUoGChoices || this.numberOfUESTC != this.requiredUESTCChoices;
        },
        buttonText: function buttonText() {
            if (this.invalidChoices) {
                return 'You must choose ' + this.requiredUoGChoices + ' UoG projects and ' + this.requiredUESTCChoices + ' UESTC projects.';
            }
            return 'Submit your choices';
        },
        numberOfUoG: function numberOfUoG() {
            return this.projects.reduce(function (prevVal, project) {
                if (!project.chosen) {
                    return prevVal;
                }
                return prevVal + (project.institution === 'UoG' ? 1 : 0);
            }, 0);
        },
        numberOfUESTC: function numberOfUESTC() {
            return this.projects.reduce(function (prevVal, project) {
                if (!project.chosen) {
                    return prevVal;
                }
                return prevVal + (project.institution === 'UESTC' ? 1 : 0);
            }, 0);
        }
    },
    created: function created() {
        Event.$on('chosen', this.toggleChoice);
    }
});
/* WEBPACK VAR INJECTION */}.call(exports, __webpack_require__(1)))

/***/ }),
/* 1 */
/***/ (function(module, exports) {

var g;

// This works in non-strict mode
g = (function() {
	return this;
})();

try {
	// This works if eval is allowed (see CSP)
	g = g || Function("return this")() || (1,eval)("this");
} catch(e) {
	// This works if the window reference is available
	if(typeof window === "object")
		g = window;
}

// g can still be undefined, but nothing to do about it...
// We return undefined, instead of nothing here, so it's
// easier to handle this case. if(!global) { ...}

module.exports = g;


/***/ }),
/* 2 */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(0);


/***/ })
/******/ ]);